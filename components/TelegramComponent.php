<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 24.10.17
 * Time: 13:52
 */

namespace Zvinger\Telegram\components;

use Telegram\Bot\Api;
use yii\base\Object;
use Zvinger\Telegram\exceptions\component\NoTokenProvidedException;
use Zvinger\Telegram\handlers\user_connection\UserConnectionInfoHandler;

class TelegramComponent extends Object
{
    private $_user_info_handler = NULL;

    /**
     * @var Api
     */
    private $_telegram_client;

    private $_telegram_bot_token;

    /**
     * @return UserConnectionInfoHandler
     */
    public function getUserConnectionInfoHandler()
    {
        if (empty($this->_user_info_handler)) {
            $this->_user_info_handler = new UserConnectionInfoHandler();
        }

        return $this->_user_info_handler;
    }

    /**
     * @return Api
     * @throws NoTokenProvidedException
     */
    public function getTelegramClient(): Api
    {
        if (empty($this->_telegram_bot_token)) {
            throw new NoTokenProvidedException();
        }
        if (empty($this->_telegram_client)) {
            $this->_telegram_client = new Api($this->_telegram_bot_token);
        }

        return $this->_telegram_client;
    }

    /**
     * @param string $telegram_bot_token
     */
    public function setTelegramBotToken($telegram_bot_token)
    {
        $this->_telegram_bot_token = $telegram_bot_token;
    }

}