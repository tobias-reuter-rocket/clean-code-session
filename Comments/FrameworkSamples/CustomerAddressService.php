<?php

namespace Common\Address\Service;


/**
 * Class CustomerAddressService
 * @package Common\Address\Service
 */
class CustomerAddressService
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * CustomerAddressService constructor.
     * @param MapperInterface $mapper
     * @param Config $config
     */
    public function __construct(MapperInterface $mapper, Config $config)
    {
        parent::__construct($mapper);

        $this->config = $config;
    }

    // ...
}
