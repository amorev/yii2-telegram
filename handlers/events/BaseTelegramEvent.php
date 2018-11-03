<?php
/**
 * Created by PhpStorm.
 * User: amorev
 * Date: 03.11.18
 * Time: 13:09
 */

namespace Zvinger\Telegram\handlers\events;


use Telegram\Bot\Objects\Update;
use yii\base\Event;

abstract class BaseTelegramEvent extends Event
{
    /**
     * @var Update
     */
    public $update;
}