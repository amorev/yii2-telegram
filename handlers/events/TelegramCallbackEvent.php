<?php
/**
 * Created by PhpStorm.
 * User: amorev
 * Date: 16.10.18
 * Time: 16:53
 */

namespace Zvinger\Telegram\handlers\events;


use Telegram\Bot\Objects\Update;
use yii\base\Event;

class TelegramCallbackEvent extends Event
{
    public $eventData;

    /**
     * @var Update
     */
    public $update;
}