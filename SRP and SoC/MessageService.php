<?php
/**
 * @author Rocket Internet SE
 * @copyright Copyright (c) 2015 Rocket Internet SE, Johannisstraße 20, 10117 Berlin, http://www.rocket-internet.de
 * @created 17.02.15 14:17
 */

namespace Common\Message\Service;

use Common\Db\Service\ServiceAbstract;
use Common\Db\Service\Traits\CrudInterface;
use Common\Db\Service\Traits\CrudTrait;
use Common\Localisation\Locale\LocaleInterface;
use Common\Message\Adapter\AbstractAdapter;
use Common\Message\Adapter\AdapterInterface;
use Common\Message\Entity\Message;
use Common\Message\MessageEntityInterface;
use Common\Message\MessageException;
use Common\Message\Recipient\EmailRecipientInterface;
use Common\Message\Recipient\SmsRecipientInterface;
use Common\Message\Template\TemplateInterface;
use Common\Notification\Service\NotificationService;
use Common\StateMachine\Plugin\StateMachine;
use DateTime;
use Phalcon\Config as PhalconConfig;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Spot\MapperInterface;

/**
 * Class MessageService
 * @package Common\Message\Service
 */
class MessageService extends ServiceAbstract implements MessageServiceInterface, LoggerAwareInterface, CrudInterface
{
    use LoggerAwareTrait;
    use CrudTrait;

    const MAX_SEND_COUNT = 5;
    const TIMEOUT_EXPONENT_BASE = 5;

    protected $adapter;
    protected $messageTemplate;
    protected $stateMachine;
    protected $locale;
    protected $instantSend;

    /**
     * @param MapperInterface $mapper
     * @param MessageTemplateService $messageTemplate
     * @param StateMachine $stateMachine
     * @param AdapterInterface $adapter
     * @param LocaleInterface $locale
     * @param string $instantSend Can be @See application.ini or SEND_ constants
     */
    public function __construct(
        MapperInterface $mapper,
        MessageTemplateService $messageTemplate,
        StateMachine $stateMachine,
        AdapterInterface $adapter = null,
        LocaleInterface $locale = null,
        $instantSend = self::SEND_WITH_STATE_MACHINE
    ) {
        parent::__construct($mapper);

        $this->adapter = $adapter;
        $this->messageTemplate = $messageTemplate;
        $this->stateMachine = $stateMachine;
        $this->locale = $locale;
        $this->instantSend = $instantSend;
    }

    /**
     * Use the adapter to send the message in email
     *   $sendOutNow - This value can be overwritten in the application.ini (by default it is not overwritten)
     *     NEVER|false = only saves the message in DB - not implicate the state machine (state: created)
     *     NOW|true  = send out the email - calls the state machine to send out the email real time (state: sent)
     *     WITH_STATE_MACHINE = calls the stateMachine to schedule the sending on the next run (state: pending)
     *
     * @param EmailRecipientInterface $recipient
     * @param TemplateInterface $template
     * @param DateTime $scheduledAt
     * @param bool|string $sendOutNow
     * @return bool
     * @throws MessageException
     * @throws \Common\StateMachine\Service\StateMachine\Exception\LockException
     */
    public function sendEmail(
        EmailRecipientInterface $recipient,
        TemplateInterface $template,
        DateTime $scheduledAt = null,
        $sendOutNow = self::SEND_WITH_STATE_MACHINE
    ) {
        $recipientEmail = $this->config->message->recipientOverride->email ?: $recipient->getEmail();

        if (!$recipientEmail) {
            throw new MessageException('Recipient has no email address');
        }

        return $this->send(self::TYPE_EMAIL, $recipientEmail, $template, $scheduledAt, $sendOutNow);
    }

    /**
     * Use the adapter to send the message in sms
     *   $sendOutNow - This value can be overwritten in the application.ini (by default it is not overwritten)
     *     NEVER|false = only saves the message in DB - not implicate the state machine (state: created)
     *     NOW|true  = send out the email - calls the state machine to send out the email real time (state: sent)
     *     WITH_STATE_MACHINE = calls the stateMachine to schedule the sending on the next run (state: pending)
     *
     * @param SmsRecipientInterface $recipient
     * @param TemplateInterface $template
     * @param DateTime $scheduledAt
     * @param bool|string $sendOutNow
     * @return bool
     * @throws MessageException
     * @throws \Common\StateMachine\Service\StateMachine\Exception\LockException
     */
    public function sendSms(
        SmsRecipientInterface $recipient,
        TemplateInterface $template,
        DateTime $scheduledAt = null,
        $sendOutNow = self::SEND_WITH_STATE_MACHINE
    ) {
        if (!$recipient->getMobilePhoneNumber()) {
            throw new MessageException('Recipient has no mobile phone number');
        }

        return $this->send(
            self::TYPE_SMS,
            $recipient->getMobilePhoneNumber(),
            $template,
            $scheduledAt,
            $sendOutNow
        );
    }

    /**
     * Use the adapter to send out the message
     *   which adapter to use is decided in the statemachine's MessageSendOutCommand based on the message type
     *
     * @param MessageEntityInterface $message
     * @return bool
     */
    public function sendOut(MessageEntityInterface $message)
    {
        if (!$this->adapter) {
            $this->log(
                'No message service adapter provided',
                $message,
                new MessageException('No adapter provided')
            );

            return false;
        }

        try {
            if ($this->logger) {
                $this->adapter->setLogger($this->logger);
            }
            $result = $this->adapter->sendOut($message, $this->messageTemplate);
        } catch (\Exception $e) {
            $this->adapter->setErrorCode(-1);
            $this->adapter->setErrorMessage($e->getMessage());
            $this->log($e->getMessage(), $message, $e);
            $result = false;
        }

        $this->updateMessage($message);

        return $result;
    }

    /**
     * @param string $type
     * @param string $recipient
     * @param TemplateInterface $template
     * @param DateTime $scheduledAt
     * @param string $sendOutNow
     * @return bool
     */
    protected function send(
        $type,
        $recipient,
        TemplateInterface $template,
        DateTime $scheduledAt = null,
        $sendOutNow = self::SEND_WITH_STATE_MACHINE
    ) {
        $sendOutNow = $this->instantSend == self::SEND_AS_REQUESTED ? $sendOutNow : $this->instantSend;
        $message = $this->createNew($recipient, $template, $type, $scheduledAt);

        return $this->triggerSending($message, $sendOutNow);
    }

    /**
     * Create the Message and saves into the DB
     *
     * @param string $destination
     * @param TemplateInterface $template
     * @param string $type
     * @param DateTime $scheduledAt
     * @return MessageEntityInterface
     * @throws MessageException
     */
    protected function createNew(
        $destination,
        TemplateInterface $template,
        $type,
        DateTime $scheduledAt = null
    ) {
        $dataArray = [
            'destination' => $destination,
            'type' => $type,
            'template_id' => $template->getTemplateId(),
            'parameters' => $template->getParameters(),
            'state' => AbstractAdapter::CREATED,
            'bcc' => $this->getTemplateBCC($template->getTemplateId())
        ];
        if ($scheduledAt) {
            $dataArray['scheduled_at'] = $scheduledAt;
        }
        $message = $this->buildEntityFromArray($dataArray);
        if (!$this->save($message)) {
            /**
             * @var Message $message
             */
            throw new MessageException(
                'Can not create message entity in DB: ' .
                implode(' ', $message->getErrorMessages())
            );
        }

        return $message;
    }

    /**
     * @param $templateId
     * @return array
     */
    protected function getTemplateBCC($templateId)
    {
        return isset($this->config->message->bcc->$templateId)
            ? (array)$this->config->message->bcc->$templateId : [];
    }

    /**
     * @param MessageEntityInterface $message
     * @param bool|string $sendOutNow
     * @return bool
     * @throws MessageException
     * @throws \Common\StateMachine\Service\StateMachine\Exception\LockException
     */
    protected function triggerSending(MessageEntityInterface $message, $sendOutNow)
    {
        if (self::SEND_NEVER === $sendOutNow || false === $sendOutNow) {
            $this->log('Message entity created', $message);

            return true;
        }

        $event = false;
        if (self::SEND_NOW === $sendOutNow || true === $sendOutNow) {
            $event = 'SendOut';
        } elseif (self::SEND_WITH_STATE_MACHINE == $sendOutNow) {
            $event = 'Pending';
        }

        if ($event === false) {
            throw new MessageException("This send option does not exists: $sendOutNow");
        }

        $this->stateMachine->get('Message')->triggerEvent($event, $message->getId(), $message);
        $this->log('Message sending triggered: ' . var_export($sendOutNow, true), $message);

        return AbstractAdapter::SENT == $message->getState()
        || AbstractAdapter::PENDING == $message->getState();
    }

    /**
     * Logs messages
     * - in file system if is enabled
     * - admin notifications
     * - pass exception to NewRelic
     *
     * @param $message
     * @param MessageEntityInterface $obj
     * @param \Exception $exception Can be an empty exception
     */
    protected function log($message, MessageEntityInterface $obj, \Exception $exception = null)
    {
        $title = 'Message Sending Error';

        if ($exception) {
            if ($this->logger) {
                $this->logger->error($message, $obj->toArray());
            }
            $this->notification->add($title, $message, NotificationService::TYPE_DANGER);
            $this->newrelic->handleException($exception);
            $this->newrelic->addParameter($title, $message);
            $this->newrelic->addParameter($title, $exception->getTraceAsString());
        } else {
            if ($this->logger) {
                $this->logger->info($message, $obj->toArray());
            }
        }
    }

    /**
     * @param MessageEntityInterface $message
     */
    protected function updateMessage(MessageEntityInterface $message)
    {
        try {
            $message->setErrorCode($this->adapter->getErrorCode());
            $message->setErrorMessage($this->adapter->getErrorMessage());
            $message->setSendCount($message->getSendCount() + 1);
            $message->setDeliveredAt(new DateTime());

            $serviceConfig = $this->getCurrentService($message->getType());
            $message->setProviderDescription($serviceConfig->name);

            if (!$this->update($message)) {
                $this->log(
                    'Can not increment send_count',
                    $message,
                    new \Exception(implode(' ', $message->getErrorMessages()))
                );
            }
        } catch (\Exception $e) {
            $this->log('Can not increment send_count', $message, $e);
        }
    }

    /**
     * @return string
     */
    public function getEntityClassName()
    {
        return '\Common\Message\Entity\Message';
    }

    /**
     * Check if not reached the max send_count
     *
     * @param MessageEntityInterface $message
     * @return bool
     */
    public function checkRetry(MessageEntityInterface $message)
    {
        return AbstractAdapter::SENT != $message->getState()
        && self::MAX_SEND_COUNT > $message->getSendCount();
    }

    /**
     * Returns the timeout for send retry in DateInterval format
     *
     * @param MessageEntityInterface $message
     * @return string
     */
    public function getRetryTimeout(MessageEntityInterface $message)
    {
        $timeoutMinutes = pow(self::TIMEOUT_EXPONENT_BASE, $message->getSendCount());

        return 'PT' . $timeoutMinutes . 'M';
    }

    /**
     * @param string $type
     * @return PhalconConfig
     */
    public function getCurrentService($type)
    {
        $service = $this->config->message->{$type};
        return $this->getService($service);
    }

    /**
     * @param string $serviceName
     * @return PhalconConfig
     */
    public function getService($serviceName)
    {
        $currentService = $this->config->{$serviceName};
        return $currentService;
    }
}
