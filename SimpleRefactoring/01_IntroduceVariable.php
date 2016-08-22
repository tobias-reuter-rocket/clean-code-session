<?php

namespace CleanCode\SimpleRefactoring;

class IntroduceVariable
{
    /**
     * @param $campsite
     * @return int
     */
    protected function calculatedRecommendedScore(array $campsite)
    {
        return 10000 * $campsite['premium']
        + 50 * 100 - $campsite['distance']
        + $campsite['rating'];
    }




    /** Refactoring Version 1 */

    /**
     * introduction of variables can help to understand the calculation faster:
     * @param $campsite
     * @return int
     */
    protected function calculatedRecommendedScoreV1($campsite)
    {
        $premiumScore = 10000 * $campsite['premium'];
        $distanceScore = 50 * 100 - $campsite['distance'];
        $ratingScore = $campsite['rating'];

        return $premiumScore + $distanceScore + $ratingScore;
    }




    /** Refactoring Version 2 */

    const SORTING_MULTIPLIER_PREMIUM = 10000;
    const SORTING_MULTIPLIER_DISTANCE = 100;
    const RADIUS_DISTANCE = 50;


    /**
     * introduction of variables can help to understand the calculation faster
     * designate the magic number
     * gives readability
     * @param $campsite
     * @return int
     */
    protected function calculatedRecommendedScoreV2($campsite)
    {
        $premiumScore = self::SORTING_MULTIPLIER_PREMIUM * $campsite['premium'];
        $distanceScore = self::RADIUS_DISTANCE * self::SORTING_MULTIPLIER_DISTANCE - $campsite['distance'];
        $ratingScore = $campsite['rating'];

        return $premiumScore + $distanceScore + $ratingScore;
    }
}
