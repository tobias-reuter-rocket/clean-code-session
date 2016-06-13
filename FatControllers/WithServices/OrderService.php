<?php

namespace CleanCode\Controllers\WithServices;

class OrderService
{
    /**
     * @param array $data
     * @throws BookingException
     */
    public function initOrder(array $data)
    {
        $booking = new Booking();
        $booking->setAdults($data['adults']);
        $booking->setChildren($data['children']);
        $booking->setPitchId($data['pitch']);
        $booking->setRoomTypeId($data['pitchType']);
        $booking->setPropertyId($data['campingId']);

        $this->pricing->flush();
        $pricing = $this->pricing->generateBookingPricingData($booking, false);
        $order = $this->createFromBooking($booking, $pricing);
        if ($order && $this->order->save($order)) {
            $this->order->updateRoomsWithRatePlan($order, $pricing);
        }
    }
}
