<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 24.10.17
 * Time: 13:59
 */

namespace Zvinger\Telegram\handlers\user_connection\models;

use yii\base\Object;
use Zvinger\Telegram\models\connection\user\TelegramUserIdConnection;

class UserInfoGetResult extends Object
{
    public $connectionId;

    public $telegramId;

    public $userId;

    public $confirmCode;

    private $_status;

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->_status;
    }

    public function getStatusString()
    {
        switch ($this->_status) {
            case TelegramUserIdConnection::STATUS_ACTIVE:
                return 'active';
            case TelegramUserIdConnection::STATUS_PENDING:
                return 'pending';
            case TelegramUserIdConnection::STATUS_DELETED:
                return 'deleted';
        }
    }
}
