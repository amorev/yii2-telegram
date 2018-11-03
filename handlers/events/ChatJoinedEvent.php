<?php
/**
 * Created by PhpStorm.
 * User: amorev
 * Date: 03.11.18
 * Time: 13:05
 */

namespace Zvinger\Telegram\handlers\events;


use Zvinger\Telegram\handlers\events\BaseTelegramEvent;

class ChatJoinedEvent extends BaseTelegramEvent
{
    public $chatId;
}