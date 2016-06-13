<?php

namespace Common\Address\Entity;

use Common\Db\Entity\EntityAbstract;
use Common\Db\Snapshot\SnapshotAwareInterface;
use Common\GeoCode\AddressInterface;
use Common\GeoCode\Geometry\Point;
use Spot\EntityInterface;
use Spot\EventEmitter;
use Spot\MapperInterface;

/**
 * Class Address
 * @package Common\Db\Entity
 *
 * @method $this setId($id);
 * @method int getId();
 *
 * @method $this setCompany($company);
 * @method string getCompany();
 *
 * @method $this setFistName($firstName);
 * @method string getFirstNamed();
 *
 * @method $this setLastName($lastName);
 * @method string getLastNamed();
 *
 * @method $this setAddress1($address);
 * @method string getAddress1();
 *
 * @method $this setAddress2($address);
 * @method string getAddress2();
 *
 * @method $this setZip($zip);
 *
 * @method $this setCity($city);
 *
 * @method $this setState($state);
 *
 * @method $this setCountry($country);
 *
 * @method $this setLocation($location);
 */
abstract class AbstractAddress extends EntityAbstract implements AddressInterface, SnapshotAwareInterface
{
    /**
     * @return array
     */
    public static function fields()
    {
        return array_merge(
            [
                'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
                // 'customer_id' => ['type' => 'integer', 'required' => true], // this is here as a reminder
                'company' => ['type' => 'string', 'required' => false],
                'first_name' => ['type' => 'string', 'required' => true],
                'last_name' => ['type' => 'string', 'required' => true],
                'address1' => ['type' => 'string', 'required' => true],
                'address2' => ['type' => 'string', 'required' => false],
                'zip' => ['type' => 'string', 'required' => true],
                'city' => ['type' => 'string', 'required' => true],
                'state' => ['type' => 'string', 'required' => false],
                'country' => ['type' => 'string', 'required' => true],
                'country_code' => ['type' => 'string', 'required' => false],
                'location' => ['type' => 'point', 'required' => false],
            ],
            parent::fields()
        );
    }

    public static function events(EventEmitter $eventEmitter)
    {
        $eventEmitter->on('beforeSave', function (EntityInterface $entity, MapperInterface $mapper) {
            if (static::getDI()->get('customerAddress')->isGeocodeEnabled()
                && ($point = static::getDI()->get('geocoder')->getPoint($entity))
            ) {
                $entity->setLocation($point);
            }
            return true;
        });

        return parent::events($eventEmitter);
    }

    /**
     * Returns address
     *
     * @return string
     */
    public function getAddress()
    {
        return implode(' ', array_filter([$this->get('address1'), $this->get('address2')]));
    }

    /**
     * Returns zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->get('zip');
    }

    /**
     * Returns city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->get('city');
    }

    /**
     * Returns state
     *
     * @return string
     */
    public function getState()
    {
        return $this->get('state');
    }

    /**
     * Returns country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->get('country');
    }

    /**
     * Returns country
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->get('country_code');
    }

    /**
     * Returns geo location point
     *
     * @return Point
     */
    public function getLocation()
    {
        return $this->get('location');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatted($format = '{first_name} {last_name} - {address1}, {zip} {city}')
    {
        return parent::getFormatted($format);
    }


}
