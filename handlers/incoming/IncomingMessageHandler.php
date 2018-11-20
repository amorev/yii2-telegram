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
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Zvinger\Telegram\components\TelegramComponent;
use Zvinger\Telegram\handlers\events\TelegramCallbackEvent;
use Zvinger\Telegram\handlers\message\TelegramMessageHandler;
use Zvinger\Telegram\handlers\events\ChatJoinedEvent;

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
        self::TEXT_ID => 'sendIdMessageReply',
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
        $callbackQuery = $update->get('callback_query');
        if ($callbackQuery) {
            return $this->handleCallBack($callbackQuery);
        }
        if (empty($message)) {
            return null;
        }
        $channelPost = $update->get('channel_post');
        if ($channelPost) {
            return false;
        }
        if ($this->isChatJoinedUpdate($update)) {
            $this->handleJoinedChat($update);
        }


        $text = $message->getText();
        $explode = explode(' ', $text);
        $command = isset($explode[0]) ? $explode[0] : null;
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

        return false;
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

        return null;
    }

    protected function sendIdMessageReply(Update $update)
    {
        return $this->_telegram_component->sendIdMessage($update->getMessage()->getChat()->getId());
    }

    /**
     * @param $callback_query Update
     * @return bool
     */
    public function handleCallBack($callback_query)
    {
        $data = Json::decode($callback_query->get('data'));
        $event = new TelegramCallbackEvent();
        $event->eventData = $data;
        $event->update = $callback_query;
        $this->_telegram_component->trigger(TelegramComponent::EVENT_CALLBACK_QUERY, $event);
        \Yii::info("Callback came to me:".print_r(func_get_args(), 1));

        return true;
    }

    /**
     * @param $update
     * @return bool
     */
    public function isChatJoinedUpdate($update): bool
    {
        $message = $update->getMessage();
        if (empty($message)) {
            return null;
        }
        $chatCreated = $message->get('group_chat_created');
        $newMember = $message->get('new_chat_member');
        if ($newMember) {
            $newMemberId = $newMember->get('id');
        } else {
            $newMemberId = null;
        }
        $meJoined = $this->_telegram_component->getBotInfo()->id === $newMemberId;
        $handleJoinedChat = $chatCreated || $meJoined;

        return $handleJoinedChat;
    }

    public function handleJoinedChat(Update $update)
    {
        $event = new ChatJoinedEvent();
        $event->chatId = $update->getMessage()->getChat()->getId();
        $this->_telegram_component->trigger(TelegramComponent::EVENT_CHAT_JOINED, $event);

        return true;
    }


}