<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 24.10.17
 * Time: 15:26
 */

namespace Zvinger\Telegram\models\connection\user\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use Zvinger\Telegram\models\connection\user\TelegramUserIdConnection;

class TelegramConnectionBehavior extends Behavior
{
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
        $this->afterSave();
    }

    public function afterUpdate()
    {
        $this->afterSave();
    }

    private function afterSave()
    {

    }
}