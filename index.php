<?php

require_once './vendor/autoload.php';

use Amp\Parallel\Worker;
use Amp\Promise;
use App\SimpleQueue;

CONST QUEUE_MESSAGES_COUNT    = 10000;
CONST QUEUE_GET_MESSAGE_LIMIT = 1000;
CONST QUEUE_EMPTY_SLEEP_MSEC  = 1000000;

$queue = make_queue(QUEUE_MESSAGES_COUNT);
handle_queue($queue);

/**
 * Алгоритм такой: получаем сообщения из очереди, группируем по аккаунту.
 * Внутри группы выполняем обработку последовательно (по условию), сами группы обрабатываем параллельно.
 *
 * @param SimpleQueue $queue
 * @throws Throwable
 */
function handle_queue(SimpleQueue $queue)
{
    while (true) {
        try {
            $promises = get_promises_by_queue($queue);
            if (empty($promises)) {
                usleep(QUEUE_EMPTY_SLEEP_MSEC);
                continue;
            }
            Promise\wait(Promise\all($promises));
            echo '.';
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
function get_promises_by_queue(SimpleQueue $queue): array
{
    $promises = [];
    $data_by_account = get_grouped_messages_by_queue($queue);
    foreach ($data_by_account as $account_id => $account_data) {
        $task = new \App\AccountConsistentTask($account_data, \App\Logger::getInstance());
        $promises[] = Worker\enqueueCallable([$task, 'run']);
    }

    return $promises;
}

function get_grouped_messages_by_queue(SimpleQueue $queue): array
{
    $data_by_account = [];
    foreach ($queue->dequeueChunk(QUEUE_GET_MESSAGE_LIMIT) as $message) {
        $message = json_decode($message, true);
        $data_by_account[$message['account_id']][] = $message;

        if (empty($data_by_account)) {
            break;
        }
    }

    return $data_by_account;
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
    return json_encode(['account_id' => mt_rand(1, 1000), 'example_token' => mt_rand(1000, 2000)]);
}
