<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.10.17
 * Time: 9:28
 */

namespace Zvinger\Telegram\handlers\incoming;

use Telegram\Bot\Objects\Update;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Zvinger\Telegram\components\TelegramComponent;
use Zvinger\Telegram\handlers\message\TelegramMessageHandler;

class IncomingMessageHandler extends BaseObject
{
    const TEXT_START = '/start';
    const TEXT_ID = '/current_chat_id';

    /**
     * @var TelegramComponent
     */
    private $_telegram_component;

    public $methods = [
        self::TEXT_START => 'sendIdMessageReply',
        self::TEXT_ID    => 'sendIdMessageReply',
    ];

    public $handlers = [];

    /**
     * IncomingMessageHandler constructor.
     * @param TelegramComponent $_telegram_component
     * @param array $handlers
     */
    public function __construct(TelegramComponent $_telegram_component, $handlers = [])
    {
        $this->_telegram_component = $_telegram_component;
        $this->handlers = $handlers;
        parent::__construct([]);
    }

    /**
     * @param $update Update
     * @return bool
     */
    public function workLongPollingUpdate($update)
    {
        $message = $update->getMessage();
        if (empty($message)) {
            $message = $update->get('edited_message');
        }
        if (empty($message)) {
            return NULL;
        }
        $channelPost = $update->get('channel_post');
        if ($channelPost) {
            return FALSE;
        }
        $callbackQuery = $update->get('callback_query');
        if ($callbackQuery) {
            return $this->handleCallBack($callbackQuery);
        }

        $text = $message->getText();
        $explode = explode(' ', $text);
        $command = isset($explode[0]) ? $explode[0] : NULL;
        if (!empty($this->methods[$command])) {
            $method = $this->methods[$command];
            if (method_exists($this, $method)) {
                return $this->{$method}($update);
            }
        }
        $handler = $this->getCommandHandler($command);
        if ($handler) {
            $handlingData = new HandlingData();
            $handlingData->messageText = $text;
            $handlingData->update = $update;
            $chat = $message->getChat();
            $handlingData->telegramId = $chat->getId();
            $handlingData->telegramUsername = $chat->getUsername();

            return $handler->handle($handlingData);
        }

        return FALSE;
    }

    /**
     * @param string $command
     * @return BaseUpdateHandler
     */
    private function getCommandHandler(string $command)
    {
        if (!empty($this->handlers[$command])) {
            $class = $this->handlers[$command];
            if (class_exists($class)) {
                $object = new $class($this->_telegram_component);
                if ($object instanceof BaseUpdateHandler) {
                    return $object;
                }
            }
        }

        return NULL;
    }

    protected function sendIdMessageReply(Update $update)
    {
        $telegramId = $update->getMessage()->getChat()->getId();
        $text = 'Добрый день! Я ' . $this->_telegram_component->telegramBotTitle . '. ' . PHP_EOL . "Текущий Telegram ID: " . PHP_EOL . "`" . $telegramId . '`';
        $message = $this->_telegram_component->createMessageHandler($telegramId, $text)->setParseMode(TelegramMessageHandler::PARSE_MARKDOWN);

        $result = $message->send();

        return !empty($result);
    }

    /**
     * @param $callback_query Update
     * @return bool
     */
    public function handleCallBack($callback_query)
    {
        $data = Json::decode($callback_query->get('data'));
        \Yii::info("Callback came to me:" . print_r(func_get_args(), 1));

        return TRUE;
    }
}