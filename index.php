<?php

require_once './vendor/autoload.php';

use Amp\Parallel\Worker;
use Amp\Promise;
use App\SimpleQueue;

CONST QUEUE_MESSAGES_COUNT    = 1000;
CONST QUEUE_GET_MESSAGE_LIMIT = 10;
CONST QUEUE_EMPTY_SLEEP_MSEC  = 1000000;

$queue = make_queue(QUEUE_MESSAGES_COUNT);
handle_queue($queue);

/**
 * @param SimpleQueue $queue
 * @throws Throwable
 */
function handle_queue(SimpleQueue $queue)
{
    while (true) {
        try {
            \App\Cache::getInstance()->set('last_task', 0);

            $promises = get_promise_messages_from_queue($queue);
            if (empty($promises)) {
                usleep(QUEUE_EMPTY_SLEEP_MSEC);
                continue;
            }
            Promise\wait(Promise\all($promises));
        } catch (Exception $e) {
            \App\Logger::getInstance()->error($e->getMessage());
            continue;
        }
    }
}

/**
 * @param SimpleQueue $queue
 * @return array
 */
function get_promise_messages_from_queue(SimpleQueue $queue)
{
    $promises = [];
    foreach ($queue->dequeueByCount(QUEUE_GET_MESSAGE_LIMIT) as $priority => $message) {
        $data = json_decode($message, true);

        $task = new \App\Task($data, \App\Cache::getInstance(), \App\Logger::getInstance());
        $task->setPriority($priority);

        $promises[] = Worker\enqueueCallable([$task, 'run']);
    }

    return $promises;
}

/**
 * @param int $msg_count
 * @return SimpleQueue
 */
function make_queue(int $msg_count): SimpleQueue
{
    $queue = new SimpleQueue();
    for ($i = 1; $i <= $msg_count; $i++) {
        $queue->enqueue(make_queue_msg());
    }

    return $queue;
}

/**
 * @param int $id
 * @return false|string
 */
function make_queue_msg()
{
    return json_encode(['account_id' => mt_rand(1, 1000)]);
}
