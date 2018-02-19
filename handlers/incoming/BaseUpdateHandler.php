<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.01.18
 * Time: 17:26
 */

namespace Zvinger\Telegram\handlers\incoming;

use Telegram\Bot\Objects\Update;
use Zvinger\Telegram\components\TelegramComponent;

abstract class BaseUpdateHandler
{
    /**
     * @var TelegramComponent
     */
    protected $_telegram_component;

    /**
     * @var HandlingData
     */
    protected $_handling_data;

    /**
     * BaseUpdateHandler constructor.
     * @param TelegramComponent $_telegram_component
     */
    public function __construct(TelegramComponent $_telegram_component)
    {
        $this->_telegram_component = $_telegram_component;
    }


    public function handle(HandlingData $handlingData)
    {
        $this->_handling_data = $handlingData;

        return $this->handleForClass($handlingData);
    }

    abstract protected function handleForClass(HandlingData $handlingData);

    protected function getAfterCommand($asArray = FALSE, $delimiter = ' ')
    {
        $result = NULL;
        $text = $this->_handling_data->messageText;
        $array = explode(' ', $text);
        unset($array[0]);
        $result = implode(' ', $array);
        if ($asArray) {
            $result = explode($delimiter, $result);
        }

        return $result;
    }
}