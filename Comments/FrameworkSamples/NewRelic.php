<?php

namespace Common\Monitoring;


/**
 * Class NewRelic
 * @package Common\Monitoring
 */
class NewRelic extends Plugin implements MonitoringInterface
{
    /**
     * @var bool
     */
    private static $active;

    /**
     * @var string
     */
    private $appname;

    /**
     * @param string $appname NewRelic application name
     */
    public function __construct($appname)
    {
        $this->appname = $appname;

        if (static::isActive()) {
            // ISSUE-fix Workaround: don't overwrite the NewRelic app name (keep the one configured by Puppet)
            //newrelic_set_appname($this->appname);
        }
    }

    // ...
}
