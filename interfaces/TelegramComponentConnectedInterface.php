<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.10.17
 * Time: 10:01
 */

namespace Zvinger\Telegram\interfaces;

use Zvinger\Telegram\components\TelegramComponent;

interface TelegramComponentConnectedInterface
{
    /**
     * @return TelegramComponent
     */
    public function getTelegramComponent(): TelegramComponent;
}