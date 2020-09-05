<?php

namespace App;

/**
 * Имитация кролика.
 *
 * Class SimpleQueue
 */
class SimpleQueue extends \SplQueue
{
    public function dequeueByCount(int $messages_count)
    {
        for ($i = 0; $i < $messages_count; $i++) {
            if ($this->isEmpty()) {
                break;
            }
            yield $this->dequeue();
        }
    }
}
