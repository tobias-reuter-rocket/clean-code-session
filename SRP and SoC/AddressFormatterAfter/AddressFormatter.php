<?php
/**
 * @author Rocket Internet SE
 * @copyright Copyright (c) 2016 Rocket Internet SE, Johannisstraße 20, 10117 Berlin, http://www.rocket-internet.de
 * @created 19.05.16 14:55
 */

namespace Common\Localisation\Formatter;

use Common\GeoCode\AddressInterface;

/**
 * Class AddressFormatter
 * Format address according to locale
 *
 * @package Common\Localisation\Formatter
 */
class AddressFormatter extends StringFormatter
{
    /**
     * @var string This pattern set here as default, should be configured in appropriate locale file
     */
    protected $pattern = '{address}';
    
    /**
     * @param AddressInterface $address
     * @return string
     */
    public function format(AddressInterface $address)
    {
        return $this->formatToString($address);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapInput($input, $match)
    {
        return $input->get($match);
    }
}
