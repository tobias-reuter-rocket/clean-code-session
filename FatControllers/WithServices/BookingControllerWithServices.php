<?php

namespace CleanCode\Controllers\WithServices;;


/**
 * Booking controller
 *
 * @package Frontend\Controller
 */
class BookingController extends ControllerBase
{
    /** @var OrderService */
    protected $order;

    /**
     * Displays booking form, initializes an Booking instance, presists it to the database
     *
     * @param int $campingId
     * @return \Phalcon\Http\ResponseInterface
     */
    public function initAction($campingId)
    {
        if (!$this->request->isPost()) {
            return;
        }

        /** @var Form $form */
        $form = $this->di->get('formFactory')
            ->createBuilder(
                BookingFormFactory::createForm($this->pitches->getAvailable($campingId, ['name']))
            )->getForm();
        $form->handleRequest();
        if (!$form->isValid()) {
            $this->flashSession->error($this->translate->trans('Could not create booking'));
            return;
        }

        try {
            $this->order->initOrder($form->getData());

        } catch (\Exception $e) {
            $errorMessage = $this->translate->trans('Could not create booking due to incorrect data');
            $this->flashSession->error($errorMessage);
            return $this->response->redirect('detailPage');
        }

        return $this->response->redirect('nextCheckOutStep');
    }
}
