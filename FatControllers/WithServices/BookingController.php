<?php

namespace CleanCode\Controllers\WithServices;

/**
 * Booking controller
 *
 * @package Frontend\Controller
 */
class BookingController extends ControllerBase
{
    /**
     * Displays booking form, initializes an Booking instance, presists it to the database
     *
     * @param int $campingId
     * @return \Phalcon\Http\ResponseInterface|void
     * @throws \Booking\Exception\BookingException
     */
    public function initAction($campingId)
    {
        $errorMessage = $this->translate->trans('Could not create booking due to incorrect date format');

        /** @var Form $form */
        $form = $this->di->get('formFactory')
            ->createBuilder(
                BookingFormFactory::createForm(
                    $this->pitches->getAvailable($campingId, ['name'])
                    //$camping->getOccupancy()
                )
            )
                ->getForm();

        if (!$this->request->isPost()) {
            return;
        }

        $form->handleRequest();
        if (!$form->isValid()) {
            $this->flashSession->error($this->translate->trans('Could not create booking'));
            return;
        }

        $booking = $form->getData();
        $booking->setAdults($form->get('adults')->getData());
        $booking->setChildren($form->get('children')->getData());
        $booking->setPitchId($form->get('pitch')->getData());
        $booking->setRoomTypeId($form->get('pitch')->getData());
        $booking->setPropertyId($campingId);

        if (!$booking->getCheckinDate() instanceof DateTime) {
            $this->flashSession->error($errorMessage);
            return;
        }

        try {
            $this->pricing->flush();
            $pricing = $this->pricing->generateBookingPricingData($booking, false);
            $order = $this->order->createFromBooking($booking, $pricing);
        } catch (\Exception $e) {
            $this->flashSession->error($errorMessage);
            return $this->response->redirect(['for' => FrontendRoutes::HOME_INDEX,]);
        }

        if ($order && $this->order->save($order)) {
            $this->order->updateRoomsWithRatePlan($order, $pricing);

            return $this->response->redirect('nextCheckOutStep');
        }

        return $this->response->redirect('detailPage');
    }
}
