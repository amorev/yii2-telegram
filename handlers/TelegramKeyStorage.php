<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.10.17
 * Time: 14:38
 */

namespace Zvinger\Telegram\handlers;

use Zvinger\Telegram\interfaces\TelegramKeyStorageInterface;

class TelegramKeyStorage implements TelegramKeyStorageInterface
{

    public function get($key)
    {
        return \Yii::$app->keyStorage->get($key);
    }

    public function set($key, $value)
    {
        return \Yii::$app->keyStorage->set($key, $value);
    }
}