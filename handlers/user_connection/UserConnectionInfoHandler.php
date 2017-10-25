<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 24.10.17
 * Time: 13:55
 */

namespace Zvinger\Telegram\handlers\user_connection;

use Bymorev\helpers\traits\connections\UserConnectedTrait;
use Zvinger\Telegram\components\TelegramComponent;
use Zvinger\Telegram\exceptions\connection\NotConnectionForUserException;
use Zvinger\Telegram\exceptions\connection\TelegramEmptyUserIdException;
use Zvinger\Telegram\exceptions\connection\TelegramWrongConfirmCodeException;
use Zvinger\Telegram\handlers\user_connection\models\UserInfoGetResult;
use Zvinger\Telegram\handlers\user_connection\models\UserInfoSetData;
use Zvinger\Telegram\models\connection\user\TelegramUserIdConnection;

class UserConnectionInfoHandler
{
    use UserConnectedTrait;

    private $_telegram_component;

    /**
     * UserConnectionInfoHandler constructor.
     * @param $_telegram_component TelegramComponent
     */
    public function __construct(TelegramComponent $_telegram_component)
    {
        $this->_telegram_component = $_telegram_component;
    }


    public function getTelegramInfo()
    {
        $object = $this->getTelegramConnectionObject();
        if (empty($object)) {
            throw new NotConnectionForUserException();
        }

        return \Yii::createObject([
            'class'       => UserInfoGetResult::class,
            'telegramId'  => $object->telegram_id,
            'userId'      => $this->getUserId(),
            'confirmCode' => $object->confirm_code,
        ]);
    }

    public function setTelegramInfo(UserInfoSetData $data)
    {
        $this->checkUser();
        $object = $this->getTelegramConnectionObject();
        if (empty($object)) {
            $object = $this->createTelegramConnectionObject($data->telegram_id);
            $this->sendConfirmationCode($object);
        } else {
            if ($object->status == $object::STATUS_PENDING) {
                $this->sendConfirmationCode($object);
            } elseif ($object->telegram_id != $data->telegram_id) {
                $this->deleteCurrentTelegramConnection();
                $object = $this->createTelegramConnectionObject($data->telegram_id);
                $this->sendConfirmationCode($object);
            }
        }

        return \Yii::createObject([
            'class'        => UserInfoGetResult::class,
            'connectionId' => $object->id,
            'telegramId'   => $data->telegram_id,
            'userId'       => $this->getUserId(),
            'confirmCode'  => $object->confirm_code,
            'status'       => $object->status,
        ]);
    }

    private function checkUser()
    {
        if (empty($this->getUserId())) {
            throw new TelegramEmptyUserIdException();
        }
    }

    /**
     * @param null $telegramId
     * @return TelegramUserIdConnection
     */
    public function getTelegramConnectionObject($telegramId = NULL)
    {
        $this->checkUser();
        $telegramUserIdConnectionQuery = TelegramUserIdConnection::find();
        $telegramUserIdConnectionQuery
            ->byUser($this->getUserId())
            ->notDeleted();
        if ($telegramId) {
            $telegramUserIdConnectionQuery->byTelegramId($telegramId);
        }

        return $telegramUserIdConnectionQuery->one();
    }

    public function deleteCurrentTelegramConnection()
    {
        $connection = $this->getTelegramConnectionObject();
        $connection->status = $connection::STATUS_DELETED;
        $connection->save();
    }

    public function sendConfirmationCode(TelegramUserIdConnection $connectionObject)
    {
        $message = 'Код подтверждения: ' . $connectionObject->confirm_code;

        return $this->_telegram_component->createMessageHandler($connectionObject->telegram_id, $message)->foreground()->send();
    }

    public function confirmTelegramId(UserInfoSetData $data)
    {
        $this->checkUser();
        $object = $this->getTelegramConnectionObject($data->telegram_id);
        if (empty($object)) {
            throw new NotConnectionForUserException();
        }
        $result = \Yii::$app->security->compareString($object->confirm_code, $data->confirmCode);
        if ($result) {
            $object->status = $object::STATUS_ACTIVE;
            $object->save();
        } else {
            throw new TelegramWrongConfirmCodeException();
        }

        return \Yii::createObject([
            'class'        => UserInfoGetResult::class,
            'connectionId' => $object->id,
            'telegramId'   => $data->telegram_id,
            'userId'       => $this->getUserId(),
            'status'       => $object->status,
        ]);
    }

    /**
     * @param $telegram_id
     * @return TelegramUserIdConnection
     */
    private function createTelegramConnectionObject($telegram_id)
    {
        $object = new TelegramUserIdConnection();
        $object->user_id = $this->getUserId();
        $object->telegram_id = $telegram_id;
        $object->confirm_code = (string)rand(100000, 999999);
        $object->save();

        return $object;
    }
}