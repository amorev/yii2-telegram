<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 20.02.18
 * Time: 21:35
 */

namespace Zvinger\Telegram\command;

use Telegram\Bot\Commands\Command;
use Zvinger\Telegram\exceptions\command\WrongTelegramCommandArgumentsException;
use Zvinger\Telegram\handlers\incoming\HandlingData;

abstract class BaseCommand extends Command
{
    protected $availableArguments = [];

    /**
     * @return array
     */
    public function getAvailableArguments(): array
    {
        return $this->availableArguments;
    }

    /**
     * @return object|HandlingData
     * @throws \yii\base\InvalidConfigException
     */
    public function getHandlingData()
    {
        $handingData = \Yii::createObject([
            'class'            => HandlingData::class,
            'update'           => $this->getUpdate(),
            'telegramId'       => $this->getUpdate()->getMessage()->getChat()->getId(),
            'messageText'      => $this->getUpdate()->getMessage()->getText(),
            'telegramUsername' => $this->getUpdate()->getMessage()->getChat()->getUsername(),
        ]);

        return $handingData;
    }

    final public function handle($arguments)
    {
        try {
            $result = $this->handleCurrent(explode(' ', $arguments));
        } catch (WrongTelegramCommandArgumentsException $e) {
            return $this->replyWithMessage([
                'text' => $e->getMessage(),
            ]);
        }

        if ($result) {
            return $this->update;
        }
    }

    /**
     * @param array $arguments
     * @return void
     */
    abstract public function handleCurrent(array $arguments): void;
}