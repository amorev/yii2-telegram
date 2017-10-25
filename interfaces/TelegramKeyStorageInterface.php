<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.10.17
 * Time: 14:39
 */

namespace Zvinger\Telegram\interfaces;

interface TelegramKeyStorageInterface
{
    public function get($key);

    public function set($key, $value);
}