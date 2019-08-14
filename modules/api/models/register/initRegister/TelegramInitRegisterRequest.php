<?php
/**
 * Created by PhpStorm.
 * User: amorev
 * Date: 14.08.2019
 * Time: 10:19
 */

namespace Zvinger\Telegram\modules\api\models\register\initRegister;


use Zvinger\BaseClasses\api\request\BaseApiRequest;

class TelegramInitRegisterRequest extends BaseApiRequest
{
    public $telegramId;
}
