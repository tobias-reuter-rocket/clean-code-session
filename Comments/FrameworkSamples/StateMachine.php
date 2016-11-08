<?php

namespace Common\StateMachine\Service\StateMachine;

interface LockAdapterInterface{}

/**
 *
 * Class StateMachine
 * @package Common\StateMachine\Service\StateMachine
 */
class StateMachine
{
    // ...

    /**
     * @param AdapterInterface $adapter
     * @param StateMachineHistoryInterface $historyLogger
     * @param string $schemaName
     * @param string $state optional
     * @throws InvalidSchemaException
     */
    public function __construct(
        AdapterInterface $adapter,
        StateMachineHistoryInterface $historyLogger,
        $schemaName,
        $state = null
    ) {

        // Sets Trigger
        $this->trigger = new Trigger();

        // Sets Payload
        $this->payload = new Payload();

        // Sets Adapter
        $this->adapter = new Adapter();
        $this->adapter->setAdapter($adapter);

        // Sets Schema Name
        $this->setSchemaName($schemaName);

        // If State is not set, then get initial State
        $this->state = new State();
        if ($state === null) {
            $state = $adapter->getInitialState();
        }
        $this->state->setState($state, $this->getAdapter());
        $this->state->setStartState($state);

        // Sets History
        $this->history = new History();
        $this->history->setHistoryLogger($historyLogger);
    }

    // ...

    /**
     * @var LockAdapterInterface
     */
    protected $lockAdapter;

    /**
     * Sets lock adapter
     *
     * @param LockAdapterInterface $lockAdapter
     * @return $this
     */
    public function setLockAdapter(LockAdapterInterface $lockAdapter)
    {
        $this->lockAdapter = $lockAdapter;
        return $this;
    }

    /**
     * Returns defined lock adapter
     *
     * @return LockAdapterInterface
     */
    public function getLockAdapter()
    {
        return $this->lockAdapter;
    }

    /**
     * Sets timeout storage
     *
     * @param TimeOutStorageInterface $timeOutStorage
     * @return $this
     */
    public function setTimeOutStorage(TimeOutStorageInterface $timeOutStorage)
    {
        $this->timeOutStorage = $timeOutStorage;
        return $this;
    }

    /**
     * Returns timeout storage
     *
     * @return TimeOutStorageInterface
     */
    public function getTimeOutStorage()
    {
        return $this->timeOutStorage;
    }

    // ...

    /**
     * Check the interval for a placeholder and if present, replace it with the value from config.
     * Example: '{{serviceName|methodName}}'
     *
     * @param $interval
     * @param $payload
     * @return mixed
     */
    protected function parseIntervalForCallback($interval, $payload = null)
    {
        /**
         * @var ConfigFilter $filter
         */
        $result = false;
        foreach ($this->getConfigFilters() as $filter) {
            $result = $filter->parse($interval, $payload);
            if (!empty($result)) {
                break;
            }
        }

        if (!$result) {
            $result = $interval;
        }

        return $result;
    }
}
