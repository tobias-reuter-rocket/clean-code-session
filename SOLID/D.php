<?php

// Dependency inversion principle

// depend upon abstractions, not concretions.

class MyAwesomeLogger implements \Psr\Log\LoggerInterface
{
    public function emergency($message, array $context = array())
    {
        // TODO: Implement emergency() method.
    }

    public function alert($message, array $context = array())
    {
        // TODO: Implement alert() method.
    }

    public function critical($message, array $context = array())
    {
        // TODO: Implement critical() method.
    }

    public function error($message, array $context = array())
    {
        // TODO: Implement error() method.
    }

    public function warning($message, array $context = array())
    {
        // TODO: Implement warning() method.
    }

    public function notice($message, array $context = array())
    {
        // TODO: Implement notice() method.
    }

    public function info($message, array $context = array())
    {
        // TODO: Implement info() method.
    }

    public function debug($message, array $context = array())
    {
        // TODO: Implement debug() method.
    }

    public function log($level, $message, array $context = array())
    {
        // TODO: Implement log() method.
    }
}

class Worker
{
    /** @var MyAwesomeLogger */
    private $logger;

    public function __construct(MyAwesomeLogger $logger)
    {
        $this->logger = $logger;
    }

    public function work(array $data)
    {
        foreach ($data as $entry) {
            $this->logger->info('Processing entry', ['entry' => $entry]);
            $this->processEntry($entry);
        }
    }

    // private function processEntry(array $entry) {}
}
