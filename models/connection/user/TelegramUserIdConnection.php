<?php

namespace Zvinger\Telegram\models\connection\user;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "telegram_user_id_connection".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $telegram_id
 * @property string $confirm_code
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 *
 * @property User $user
 */
class TelegramUserIdConnection extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_PENDING = 2;
    const STATUS_DELETED = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'telegram_user_id_connection';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['telegram_id'], 'string', 'max' => 40],
            [['confirm_code'], 'string', 'max' => 6],
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->status = self::STATUS_PENDING;
        }

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }


    public function behaviors()
    {
        return [
            TimestampBehavior::class,
//            TelegramConnectionBehavior::class,
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'user_id'      => 'User ID',
            'telegram_id'  => 'Telegram ID',
            'confirm_code' => 'Confirm Code',
            'created_at'   => 'Created At',
            'updated_at'   => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return TelegramUserIdConnectionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TelegramUserIdConnectionQuery(get_called_class());
    }
}
