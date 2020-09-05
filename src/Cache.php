<?php
/**
 * Created by PhpStorm.
 * User: mkiselev
 * Date: 05.09.2020
 * Time: 07:41
 */

namespace App;

/**
 * Имитация мемкеша.
 *
 * Class Cache
 * @package App
 */
class Cache
{
    const DIR = __DIR__ . '/../var/cache/';

    private static $instance = null;

    private function __construct()
    {
        if (! file_exists(self::DIR)) {
            throw new \Exception('Cache dir not exist.');
        }
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function get(string $key)
    {
        return file_get_contents($this->getFileName($key));
    }

    public function set(string $key, $value)
    {
        file_put_contents($this->getFileName($key), $value);
    }

    private function getFileName(string $key)
    {
        return self::DIR . $key;
    }
}
