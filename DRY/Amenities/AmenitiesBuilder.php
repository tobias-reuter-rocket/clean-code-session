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

    public function versionTwoByDeveloperB($request)
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
        $utilities = $request->getQuery('utilities', [], []);
        if (!empty($utilities) && !is_array($utilities)) {
            $utilities = htmlspecialchars($utilities);
            $utilities = explode(',', $utilities);
        }
        $rules = $request->getQuery('rules', [], []);
        if (!empty($rules) && !is_array($rules)) {
            $rules = htmlspecialchars($rules);
            $rules = explode(',', $rules);
        }
        $activities = $request->getQuery('activities', [], []);
        if (!empty($activities) && !is_array($activities)) {
            $activities = htmlspecialchars($activities);
            $activities = explode(',', $activities);
        }

        // other code left out

        return [
            'amenities' => $amenities,
            'leisure' => $leisure,
            'utilities' => $utilities,
            'rules' => $rules,
            'activities' => $activities,
        ];
    }
}