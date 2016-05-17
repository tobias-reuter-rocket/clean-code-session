<?php


namespace CleanCode\DRY;


class AmenitiesBuilder
{
    public function versionTwoByDeveloperA($request)
    {
        // other code left out

        $amenities = $request->getQuery('amenities', [], []);
        if (!empty($amenities) && !is_array($amenities)) {
            $amenities = htmlspecialchars($amenities, ENT_HTML5);
            $amenities = explode(',', $amenities);
        }
        $leisure = $request->getQuery('leisure', [], []);
        if (!empty($leisure) && !is_array($leisure)) {
            $leisure = htmlspecialchars($leisure, ENT_HTML5);
            $leisure = explode(',', $leisure);
        }

        // other code left out

        return [
            'amenities' => $amenities,
            'leisure' => $leisure,
        ];
    }

    public function versionOneBranched($request)
    {
        // other code left out

        $amenities = $request->getQuery('amenities', [], []);
        if (!empty($amenities) && !is_array($amenities)) {
            $amenities = htmlspecialchars($amenities);
            $amenities = explode(',', $amenities);
        }
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
}