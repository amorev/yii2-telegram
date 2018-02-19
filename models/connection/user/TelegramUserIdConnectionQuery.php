<?php

namespace Zvinger\Telegram\models\connection\user;

use Bymorev\helpers\traits\query\UserQueryTrait;

/**
 * This is the ActiveQuery class for [[TelegramUserIdConnection]].
 *
 * @see TelegramUserIdConnection
 */
class TelegramUserIdConnectionQuery extends \yii\db\ActiveQuery
{
    use UserQueryTrait;

    public function byTelegramId($telegram_id)
    {
        return $this->andWhere(['telegram_id' => $telegram_id]);
    }

    public function active()
    {
        return $this->byStatus(TelegramUserIdConnection::STATUS_ACTIVE);
    }

    public function notDeleted()
    {
        return $this->andWhere(['<>', 'status', TelegramUserIdConnection::STATUS_DELETED]);
    }

    public function byStatus($status)
    {
        return $this->andWhere(['status' => $status]);
    }

    /**
     * @param $user_id
     * @return $this
     */
    public function byUserId($user_id)
    {
        return $this->byUser($user_id);
    }

    /**
     * @inheritdoc
     * @return TelegramUserIdConnection[]|array
     */
    public function all($db = NULL)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TelegramUserIdConnection|array|null
     */
    public function one($db = NULL)
    {
        return parent::one($db);
    }
}
