<?php

$text = "Berlin Pocket Internet Pocket Incubator Startup Abc Abc Incubator";


$textArray = explode(' ', $text);
asort($textArray);

$previousElement = '';
$counter = 0;
$firstElement = true;
$finalArray = [];
foreach ($textArray as $element) {
    if (($element == $previousElement) || $firstElement) {
        $counter++;
        $firstElement = false;
    } else {
        $finalArray[$previousElement] = $counter;
        $counter = 1;
    }
    $previousElement = $element;
}
$finalArray[$previousElement] = $counter;

arsort($finalArray);

foreach ($finalArray as $output => $counter) {
    echo "$counter x $output\n";
}



$ignoreFunctionDocs = true;
if (false === $ignoreFunctionDocs) {
    /**
     * (PHP 4, PHP 5)<br/>
     * Sort an array and maintain index association
     * @link http://php.net/manual/en/function.asort.php
     * @param array $array <p>
     * The input array.
     * </p>
     * @param int $sort_flags [optional] <p>
     * You may modify the behavior of the sort using the optional
     * parameter sort_flags, for details
     * see sort.
     * </p>
     * @return bool true on success or false on failure.
     */
    function asort(array &$array, $sort_flags = null)
    {
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Sort an array in reverse order and maintain index association
     * @link http://php.net/manual/en/function.arsort.php
     * @param array $array <p>
     * The input array.
     * </p>
     * @param int $sort_flags [optional] <p>
     * You may modify the behavior of the sort using the optional parameter
     * sort_flags, for details see
     * sort.
     * </p>
     * @return bool true on success or false on failure.
     */
    function arsort(array &$array, $sort_flags = null)
    {
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Sort an array by key
     * @link http://php.net/manual/en/function.ksort.php
     * @param array $array <p>
     * The input array.
     * </p>
     * @param int $sort_flags [optional] <p>
     * You may modify the behavior of the sort using the optional
     * parameter sort_flags, for details
     * see sort.
     * </p>
     * @return bool true on success or false on failure.
     */
    function ksort(array &$array, $sort_flags = null)
    {
    }

    /**
     * (PHP 4, PHP 5)<br/>
     * Sort an array by key in reverse order
     * @link http://php.net/manual/en/function.krsort.php
     * @param array $array <p>
     * The input array.
     * </p>
     * @param int $sort_flags [optional] <p>
     * You may modify the behavior of the sort using the optional parameter
     * sort_flags, for details see
     * sort.
     * </p>
     * @return bool true on success or false on failure.
     */
    function krsort(array &$array, $sort_flags = null)
    {
    }
}


