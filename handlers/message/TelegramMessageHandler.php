<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 24.10.17
 * Time: 15:03
 */

namespace Zvinger\Telegram\handlers\message;

use Zvinger\Telegram\components\TelegramComponent;
use Zvinger\Telegram\exceptions\message\EmptyChatIdException;
use Zvinger\Telegram\exceptions\message\EmptyMessageTextException;

class TelegramMessageHandler
{
    const PARSE_HTML = 'HTML';
    const PARSE_MARKDOWN = 'Markdown';

    private $_text;

    private $_receiver_chat_id;

    private $_parse_mode = self::PARSE_HTML;

    private $_background = TRUE;

    private $_telegram_component;

    /**
     * TelegramMessageHandler constructor.
     * @param $telegramComponent
     * @param $_receiver_chat_id
     * @param $_text
     */
    public function __construct(TelegramComponent $telegramComponent, $_receiver_chat_id = NULL, $_text = NULL)
    {
        $this->_text = $_text;
        $this->_receiver_chat_id = $_receiver_chat_id;
        $this->_telegram_component = $telegramComponent;
    }

    /**
     * @param mixed $text
     * @return $this
     */
    public function setText($text)
    {
        $this->_text = $text;

        return $this;
    }

    /**
     * @param mixed $_receiver_chat_id
     * @return $this
     */
    public function setReceiverChatId($_receiver_chat_id)
    {
        $this->_receiver_chat_id = $_receiver_chat_id;

        return $this;
    }

    public function send()
    {
        if (empty($this->_receiver_chat_id)) {
            throw new EmptyChatIdException();
        }
        if (empty($this->_text)) {
            throw new EmptyMessageTextException();
        }

        //  todo сделать получение результата отправки сообщения у фоновых отправок сообщений
        return $this->_telegram_component->getTelegramClient()->sendMessage([
            'chat_id'    => $this->_receiver_chat_id,
            'text'       => $this->_text,
            'parse_mode' => $this->_parse_mode,
        ]);
    }

    public function foreground()
    {
        $this->_background = FALSE;

        return $this;
    }

    public function background()
    {
        $this->_background = TRUE;

        return $this;
    }

    /**
     * @param string $parse_mode
     * @return TelegramMessageHandler
     */
    public function setParseMode(string $parse_mode): TelegramMessageHandler
    {
        $this->_parse_mode = $parse_mode;

        return $this;
    }
}