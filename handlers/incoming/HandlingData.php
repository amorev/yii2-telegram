<?php

namespace Zvinger\Telegram\handlers\incoming;

use Telegram\Bot\Objects\Update;

class HandlingData
{
    public $messageText;

    public $telegramId;

    /**
     * @var Update
     */
    public $update;

    public $telegramUsername;
}