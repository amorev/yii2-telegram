<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.10.17
 * Time: 9:28
 */

namespace Zvinger\Telegram\handlers\incoming;

use Telegram\Bot\Objects\Update;
use yii\helpers\Json;
use Zvinger\Telegram\components\TelegramComponent;
use Zvinger\Telegram\handlers\message\TelegramMessageHandler;

class IncomingMessageHandler
{
    const TEXT_START = '/start';
    const TEXT_ID = '/current_chat_id';

    /**
     * @var TelegramComponent
     */
    private $_telegram_component;

    /**
     * IncomingMessageHandler constructor.
     * @param TelegramComponent $_telegram_component
     */
    public function __construct(TelegramComponent $_telegram_component)
    {
        $this->_telegram_component = $_telegram_component;
    }

    /**
     * @param $update Update
     * @return bool
     */
    public function workLongPollingUpdate($update)
    {
        $message = $update->getMessage();
        $channelPost = $update->get('channel_post');
        if ($channelPost) {
            return FALSE;
        }
        $callbackQuery = $update->get('callback_query');
        if ($callbackQuery) {
            return $this->handleCallBack($callbackQuery);
        }

        $text = $message->getText();
        if ($text == static::TEXT_START || $text == self::TEXT_ID) {
            return $this->sendIdMessageReply($update);
        }

        return FALSE;
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