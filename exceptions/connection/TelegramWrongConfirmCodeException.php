<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.10.17
 * Time: 10:21
 */

namespace Zvinger\Telegram\exceptions\connection;

use Throwable;
use Zvinger\Telegram\exceptions\BaseTelegramUserException;

class TelegramWrongConfirmCodeException extends BaseTelegramUserException
{
    public function __construct($message = "", $code = 0, Throwable $previous = NULL)
    {
        $message = "Wrong confirm code";
        parent::__construct($message, $code, $previous);
    }

}