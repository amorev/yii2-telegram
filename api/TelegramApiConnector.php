<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 20.04.18
 * Time: 9:46
 */

namespace Zvinger\Telegram\api;


use Telegram\Bot\TelegramClient;

class TelegramApiConnector extends TelegramClient
{
    private $_baseBotUrl = NULL;

    public function getBaseBotUrl()
    {
        if (empty($this->_baseBotUrl)) {
            $this->_baseBotUrl = static::BASE_BOT_URL;
        }

        return $this->_baseBotUrl;
    }

    public function setBaseBotUrl($url)
    {
        $this->_baseBotUrl = $url;
    }
}