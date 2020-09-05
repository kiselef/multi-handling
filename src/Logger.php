<?php

namespace App;

use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

/**
 * Class Logger
 * @package App
 */
class Logger
{
    private $logger = null;
    private static $instance = null;

    private function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getInstance(): self
    {
        if (static::$instance instanceof LoggerInterface) {
            return static::$instance;
        }

        $log = new \Monolog\Logger(static::class);
        $log->pushHandler(new StreamHandler(__DIR__ . '/../var/log/debug.log', \Monolog\Logger::DEBUG));
        static::$instance = new static($log);

        return static::$instance;
    }

    public function debug(string $message)
    {
        $this->logger->debug($message);
    }

    public function error(string $message)
    {
        $this->logger->error($message);
    }
}
