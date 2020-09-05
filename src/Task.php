<?php

namespace App;

/**
 * Class Task
 * @package App
 */
class Task
{
    /**
     * Интервал проверки возмонжости исполнения текущей таски относительно ее порядка в очереди.
     */
    const PRIORITY_WAITING_MSEC = 200000;

    private $data = [];
    private $priority = null;
    private $cache;
    private $logger;

    public function __construct(array $data, Cache $cache, Logger $logger)
    {
        $this->data = $data;

        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }

    public function run(): void
    {
        if (! is_null($this->priority)) {
            $this->waitPriority();
        }

        $this->cache->set('last_task', $this->getIncreasedPriority());
        $this->logger->debug(json_encode($this->data + ['priority' => $this->priority]));

        sleep(1);
    }

    /**
     * Ждет пока выполнятся процессы с более низким приоретом.
     */
    private function waitPriority(): void
    {
        while (true) {
            if ($this->priority <= $this->cache->get('last_task')) {
                break;
            }
            usleep(self::PRIORITY_WAITING_MSEC);
            continue;
        }
    }

    private function getIncreasedPriority(int $step = 1)
    {
        return $this->priority + $step;
    }
}
