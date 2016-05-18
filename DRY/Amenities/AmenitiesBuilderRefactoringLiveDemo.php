<?php


namespace CleanCode\DRY;


class AmenitiesBuilder
{
    public function versionOne($request)
    {
        // other code left out

        $amenities = $this->getAmenity($request, 'amenities');
        $leisure = $this->getAmenity($request, 'leisure');
        $utilities = $this->getAmenity($request, 'utilities');
        $rules = $this->getAmenity($request, 'rules');
        $activities = $this->getAmenity($request, 'activities');

        // other code left out

        return [
            'amenities' => $amenities,
            'leisure' => $leisure,
            'utilities' => $utilities,
            'rules' => $rules,
            'activities' => $activities,
        ];
    }

    /**
     * @param $request
     * @param $amenityName
     * @return array|string
     */
    private function getAmenity($request, $amenityName)
    {
        $amenities = $request->getQuery($amenityName, [], []);
        if (!empty($amenities) && !is_array($amenities)) {
            $amenities = htmlspecialchars($amenities, ENT_HTML5);
            $amenities = explode(',', $amenities);
        }

        return $amenities;
    }
}