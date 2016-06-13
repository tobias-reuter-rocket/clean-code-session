<?php

namespace Frontend\Controller;

use Common\Controller\ControllerBase;
use Common\Message\Recipient\GenericRecipient;
use Common\Message\Template\EmailChangeTemplate;
use Common\Purchase\Service\PurchaseService;
use Common\Token\Service\TokenService;
use Common\Form\PasswordReset;
use Frontend\FrontendRoutes;
use Common\Address\Entity\CustomerAddress;
use Common\Customer\Entity\Customer;
use Common\Payment\Entity\PaymentSavedMethod;
use Common\Navigation\Navigation;
use Common\Navigation\Node;
use Phalcon\Http\ResponseInterface;

/**
 * Class AccountController
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) // TODO refactor and fix PHPMD.TooManyPublicMethods
 *
 * @package Frontend\Controller
 */
class AccountController extends FrontendControllerBase
{
    //...

    public function defaultAddressAction($id, $type)
    {
        /** @var Customer $customer */
        $customer = $this->auth->getUser();
        $address = $this->getAddress($id, $customer);
        if ($address) {
            $formatted = ['address' => $this->format->formatAddress($address)];
            switch ($type) {
                case 'invoice':
                    $customer->setInvoiceAddressId($id);
                    $this->customer->save($customer);
                    $this->flashSession->success(
                        $this->translate->_('frontend.myAccount.markDefaultDeliverySuccess', $formatted)
                    );
                    break;
                case 'delivery':
                    $customer->setDeliveryAddressId($id);
                    $this->customer->save($customer);
                    $this->flashSession->success(
                        $this->translate->_('frontend.myAccount.markDefaultDeliverySuccess', $formatted)
                    );
                    break;
                default:
                    $this->flashSession->error($this->translate->_('frontend.myAccount.markDefaultFail'));
            }
        }
        return $this->response->redirect(['for' => FrontendRoutes::ACCOUNT_ADDRESS]);
    }

    // ...
}
