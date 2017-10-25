<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.10.17
 * Time: 11:34
 */

namespace Zvinger\Telegram\exceptions\api;

use Throwable;
use yii\web\HttpException;

class TelegramWrongChatException extends HttpException
{
    public function __construct($message = "", $code = 0, Throwable $previous = NULL)
    {
        $message = 'Wrong chat id';
        parent::__construct(400, $message, $code, $previous);
    }

}