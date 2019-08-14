<?php
/**
 * Created by PhpStorm.
 * User: amorev
 * Date: 14.08.2019
 * Time: 10:26
 */

namespace Zvinger\Telegram\modules\api\models\register\saveCode;


use Zvinger\BaseClasses\api\request\BaseApiRequest;

class TelegramSaveCodeRequest extends BaseApiRequest
{
    public $telegramId;

    public $code;
}
