<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 24.10.17
 * Time: 15:26
 */

namespace Zvinger\Telegram\models\connection\user\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use Zvinger\Telegram\components\TelegramComponent;
use Zvinger\Telegram\handlers\user_connection\UserConnectionInfoHandler;
use Zvinger\Telegram\models\connection\user\TelegramUserIdConnection;

class TelegramConnectionBehavior extends Behavior
{
    public $telegramComponentName = 'telegramComponent';

    /**
     * @var TelegramComponent
     */
    private $_telegram_component;

    /**
     * @var TelegramUserIdConnection
     */
    public $owner;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    public function afterInsert()
    {
        $connection = $this->owner;
        $status = $connection->status;
        switch ($status) {
            case $connection::STATUS_PENDING:
                $this->sendConfirmationCode();
                break;
        }
        $this->afterSave();
    }

    private function sendConfirmationCode()
    {
        $this->getTelegramComponent()->getUserConnectionInfoHandler()->sendConfirmationCode($this->owner);
    }

    public function afterUpdate()
    {
        $this->afterSave();
    }

    private function afterSave()
    {

    }


    private function getTelegramComponent()
    {
        if (empty($this->_telegram_component)) {
            if (!empty($this->telegramComponentName)) {
                $this->_telegram_component = Yii::$app->get($this->telegramComponentName);
            }
        }

        return $this->_telegram_component;
    }
}