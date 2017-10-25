<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 24.10.17
 * Time: 13:52
 */

namespace Zvinger\Telegram\components;

use Telegram\Bot\Api;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\Inflector;
use Zvinger\Telegram\console\command\TelegramConsoleController;
use Zvinger\Telegram\exceptions\component\NoTokenProvidedException;
use Zvinger\Telegram\handlers\incoming\IncomingMessageHandler;
use Zvinger\Telegram\handlers\message\TelegramMessageHandler;
use Zvinger\Telegram\handlers\TelegramKeyStorage;
use Zvinger\Telegram\handlers\user_connection\UserConnectionInfoHandler;
use Zvinger\Telegram\interfaces\TelegramKeyStorageInterface;

class TelegramComponent extends Object implements BootstrapInterface
{
    private $_user_info_handler = NULL;

    /**
     * @var Api
     */
    private $_telegram_client;

    private $_telegram_bot_token;

    private $_key_storage_component_name;

    private $_key_storage;

    public $keyStorageLastUpdateIdKey = 'Telegram.LongPolling.LastUpdateId';

    public $telegramBotTitle;


    /**
     * @return UserConnectionInfoHandler
     */
    public function getUserConnectionInfoHandler()
    {
        if (empty($this->_user_info_handler)) {
            $this->_user_info_handler = new UserConnectionInfoHandler($this);
        }

        return $this->_user_info_handler;
    }

    /**
     * @param $telegramId
     * @param $message
     * @return TelegramMessageHandler
     */
    public function createMessageHandler($telegramId = NULL, $message = NULL)
    {
        return new TelegramMessageHandler($this, $telegramId, $message);
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

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $app->controllerMap[$this->getCommandId()] = [
                'class'             => TelegramConsoleController::class,
                'telegramComponent' => $this,
            ];
        }
    }


    private $_incoming_message_handler;

    public function getIncomingMessageHandler()
    {
        if (empty($this->_incoming_message_handler)) {
            $this->_incoming_message_handler = new IncomingMessageHandler($this);
        }

        return $this->_incoming_message_handler;
    }

    public function getLastUpdateId()
    {
        return $this->getKeyStorage()->get($this->keyStorageLastUpdateIdKey);
    }

    public function setLastUpdateId($id)
    {
        return $this->getKeyStorage()->set($this->keyStorageLastUpdateIdKey, $id);
    }

    /**
     * @return string command id
     * @throws
     */
    protected function getCommandId()
    {
        foreach (\Yii::$app->getComponents(FALSE) as $id => $component) {
            if ($component === $this) {
                return Inflector::camel2id($id);
            }
        }
        throw new InvalidConfigException('Telegram Component must be an application component.');
    }

    /**
     * @param mixed $key_storage_component_name
     */
    public function setKeyStorageComponentName($key_storage_component_name)
    {
        $this->_key_storage_component_name = $key_storage_component_name;
    }

    private function getKeyStorage(): TelegramKeyStorageInterface
    {
        if (empty($this->_key_storage)) {
            if (!empty($this->_key_storage_component_name)) {
                $this->_key_storage = \Yii::$app->get($this->_key_storage_component_name);
            } else {
                $this->_key_storage = new TelegramKeyStorage();
            }
        }

        return $this->_key_storage;
    }
}