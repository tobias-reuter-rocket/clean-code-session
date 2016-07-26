<?php

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
