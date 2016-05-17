<?php


namespace CleanCode\DRY;


class AmenitiesBuilder
{
    public function versionOne($request)
    {
        // other code left out

        $amenities = $this->processAmenities($request, 'amenities');
        $leisure = $request->getQuery('leisure', [], []);
        if (!empty($leisure) && !is_array($leisure)) {
            $leisure = htmlspecialchars($leisure);
            $leisure = explode(',', $leisure);
        }

        // other code left out

        return [
            'amenities' => $amenities,
            'leisure' => $leisure,
        ];
    }

    /**
     * @param $request
     * @param $amenityName
     * @return array|string
     */
    private function processAmenities($request, $amenityName)
    {
        $amenities = $request->getQuery($amenityName, [], []);
        if (!empty($amenities) && !is_array($amenities)) {
            $amenities = htmlspecialchars($amenities);
            $amenities = explode(',', $amenities);
        }

        return $amenities;
    }
}