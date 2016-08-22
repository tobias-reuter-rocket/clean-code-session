<?php

namespace CleanCode\SimpleRefactoring;

class ExtractClassFromParameter
{
    /**
     * @param $street
     * @param $zip
     * @param $town
     * @param $region
     * @param $country
     * @return string
     */
    public function getFullAddress($street, $zip, $town, $region, $country)
    {
        return sprintf('%s, %s %s, %s in %s', $street, $zip, $town, $region, $country);
    }




    /** Refactoring */

    /**
     * Here can be improved by an Extract Class refactoring from parameter readability:
     * @param Address $address
     * @return string
     */
    public function getFullAddress2(Address $address)
    {
        return sprintf(
            '%s, %s %s, %s',
            $address->getStreet(),
            $address->getZip(),
            $address->getTown(),
            $address->getRegion()
        );
    }

    /**
     * Benefits of Using Extract Class from parameter
     * - more readable to make.
     * - results in a higher level of abstraction .
     */
}



class Address
{
    private $street;
    private $zip;
    private $town;
    private $region;
    private $country;

    public function __construct($street, $zip, $town, $region, $country)
    {
        $this->street = $street;
        $this->zip = $zip;
        $this->town = $town;
        $this->region = $region;
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @return mixed
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }
}
