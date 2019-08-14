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
use yii\base\BaseObject;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;
use Zvinger\Telegram\api\TelegramApiClient;
use Zvinger\Telegram\api\TelegramApiConnector;
use Zvinger\Telegram\console\command\TelegramConsoleController;
use Zvinger\Telegram\exceptions\component\NoTokenProvidedException;
use Zvinger\Telegram\handlers\events\ChatJoinedEvent;
use Zvinger\Telegram\handlers\incoming\BaseUpdateHandler;
use Zvinger\Telegram\handlers\incoming\IncomingMessageHandler;
use Zvinger\Telegram\handlers\message\TelegramMessageHandler;
use Zvinger\Telegram\handlers\TelegramKeyStorage;
use Zvinger\Telegram\handlers\user_connection\UserConnectionInfoHandler;
use Zvinger\Telegram\interfaces\TelegramKeyStorageInterface;
use Zvinger\Telegram\models\bot\TelegramBotInfo;
use Zvinger\Telegram\modules\api\TelegramApiModule;

class TelegramComponent extends Component implements BootstrapInterface
{
    const EVENT_CALLBACK_QUERY = 'EVENT_CALLBACK_QUERY';
    const EVENT_CHAT_JOINED = 'EVENT_CHAT_JOINED';
    private $_user_info_handler = null;

    /**
     * @var TelegramApiClient
     */
    private $_telegram_client;

    private $_telegram_bot_token;

    private $_key_storage_component_name;

    private $_key_storage;

    const BASE_PROXY_API = 'http://telegram-proxy.obvu.ru/bot';

    private $_bot_api_url;

    public $keyStorageLastUpdateIdKey = 'Telegram.LongPolling.LastUpdateId';

    public $telegramBotTitle;

    public $namedContacts = [];

    public $messageHandlers = [];

    public $telegramApiClient = TelegramApiClient::class;

    public $commands;

    public $sendIdOnJoin = true;

    /**
     * @var BaseUpdateHandler
     */
    public $allMessageHandler = null;

    public function init()
    {
        $this->on(
            static::EVENT_CHAT_JOINED,
            function (ChatJoinedEvent $event) {
                if ($this->sendIdOnJoin) {
                    $this->sendIdMessage($event->chatId);
                }
            }
        );
        parent::init();
    }

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
    public function createMessageHandler($telegramId = null, $message = null)
    {
        return new TelegramMessageHandler($this, $telegramId, $message);
    }

    /**
     * @param $who
     * @param $message
     * @return \Telegram\Bot\Objects\Message
     * @throws NoTokenProvidedException
     * @throws \Zvinger\Telegram\exceptions\message\EmptyChatIdException
     * @throws \Zvinger\Telegram\exceptions\message\EmptyMessageTextException
     */
    public function message($who, $message)
    {
        return $this->createMessageHandler($who, $message)->send();
    }

    /**
     * @return TelegramApiClient
     * @throws NoTokenProvidedException
     */
    public function getTelegramClient()
    {
        if (empty($this->_telegram_bot_token)) {
            throw new NoTokenProvidedException();
        }
        if (empty($this->_telegram_client)) {
            $this->_telegram_client = new $this->telegramApiClient($this->_telegram_bot_token);
            if ($this->_telegram_client instanceof TelegramApiClient) {
                $this->_telegram_client->setClientBotApiUrl($this->getBotApiUrl());
            }
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
     * @throws InvalidConfigException
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $app->controllerMap[$this->getCommandId()] = [
                'class' => TelegramConsoleController::class,
                'telegramComponent' => $this,
            ];
        } elseif ($app instanceof \yii\web\Application) {
            $app->setModule(
                'telegram',
                [
                    'class' => TelegramApiModule::class,
                ]
            );
        }
    }


    private $_incoming_message_handler;

    public function getIncomingMessageHandler()
    {
        if (empty($this->_incoming_message_handler)) {
            $this->_incoming_message_handler = new IncomingMessageHandler($this, $this->messageHandlers);
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
        foreach (\Yii::$app->getComponents(false) as $id => $component) {
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

    /**
     * @param $webHook
     * @throws NoTokenProvidedException
     */
    public function handleCommands($webHook)
    {
        $api = $this->getTelegramClient();
        $api->addCommands($this->commands);
        if (!$webHook) {
            while (true) {
                $api->commandsHandler(false, 30);
            }
        } else {
            $api->commandsHandler(true);
        }
    }

    /**
     * @param mixed $bot_api_url
     */
    public function setBotApiUrl($bot_api_url): void
    {
        $this->_bot_api_url = $bot_api_url;
    }

    private function getBotApiUrl()
    {
        if (empty($this->_bot_api_url)) {
            $this->_bot_api_url = static::BASE_PROXY_API;
        }

        return $this->_bot_api_url;
    }

    /**
     * @var TelegramBotInfo
     */
    private $_bot_info;

    public function getBotInfo(): TelegramBotInfo
    {
        if (empty($this->_bot_info)) {
            $this->_bot_info = new TelegramBotInfo();
            $data = $this->getTelegramClient()->getMe();
            $this->_bot_info->first_name = $data->get('first_name');
            $this->_bot_info->username = $data->get('username');
            $this->_bot_info->id = $data->get('id');
        }

        return $this->_bot_info;
    }

    /**
     * @param $telegramId
     * @return bool
     * @throws \Zvinger\Telegram\exceptions\component\NoTokenProvidedException
     * @throws \Zvinger\Telegram\exceptions\message\EmptyChatIdException
     * @throws \Zvinger\Telegram\exceptions\message\EmptyMessageTextException
     */
    public function sendIdMessage($telegramId): bool
    {
        $text = 'Добрый день! Я '.$this->getBotInfo(
            )->first_name.'. '.PHP_EOL."Текущий Telegram ID: ".PHP_EOL."`".$telegramId.'`';
        $message = $this->createMessageHandler($telegramId, $text)->setParseMode(
            TelegramMessageHandler::PARSE_MARKDOWN
        );

        $result = $message->send();

        return !empty($result);
    }
}
