<?php

namespace App;

/**
 * Имитация кролика.
 *
 * Class SimpleQueue
 */
class SimpleQueue extends \SplQueue
{
    /**
     * @param int $chunk_size Число сообщений за раз.
     * @return \Generator
     */
    public function dequeueChunk(int $chunk_size)
    {
        for ($i = 0; $i < $chunk_size; $i++) {
            if ($this->isEmpty()) {
                break;
            }
            yield $this->dequeue();
        }
    }
}
