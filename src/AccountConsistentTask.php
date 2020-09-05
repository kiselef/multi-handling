<?php

namespace App;

/**
 * Class AccountConsistentTask
 * @package App
 */
class AccountConsistentTask
{

    private $data = [];
    private $logger;

    public function __construct(array $data, Logger $logger)
    {
        $this->data = $data;

        $this->logger = $logger;
    }

    /**
     * Внутри группы события обрабатываются последовательно.
     */
    public function run(): void
    {
        foreach ($this->data as $data) {
            $this->doSomething($data);
            $this->logger->debug(json_encode($data));
        }
    }

    private function doSomething(array $data): void
    {
        sleep(1);
    }
}
