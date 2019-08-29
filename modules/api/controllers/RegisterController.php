<?php
/**
 * Created by PhpStorm.
 * User: amorev
 * Date: 14.08.2019
 * Time: 10:03
 */

namespace Zvinger\Telegram\modules\api\controllers;


use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use Zvinger\BaseClasses\api\controllers\BaseApiController;
use Zvinger\Telegram\exceptions\connection\TelegramWrongConfirmCodeException;
use Zvinger\Telegram\handlers\user_connection\models\UserInfoSetData;
use Zvinger\Telegram\handlers\user_connection\UserConnectionInfoHandler;
use Zvinger\Telegram\modules\api\models\register\initRegister\TelegramInitRegisterRequest;
use Zvinger\Telegram\modules\api\models\register\saveCode\TelegramSaveCodeRequest;

class RegisterController extends BaseApiController
{
    public function behaviors()
    {
        $old = parent::behaviors();
        $behaviors = [];
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        $array_merge = array_merge($old, $behaviors);

        return $array_merge;
    }

    public function actionCurrentData()
    {
        $info = $this->prepareHandler()->getTelegramInfo();

        return [
            'username' => 'User #'.$info->telegramId,
            'telegramId' => $info->telegramId,
        ];
    }

    public function actionInitRegister()
    {
        $request = TelegramInitRegisterRequest::createRequest();
        if (empty($request->telegramId)) {
            throw new BadRequestHttpException("TelegramID РЅРµ РјРѕР¶РµС‚ Р±С‹С‚СЊ РїСѓСЃС‚С‹Рј");
        }
        $this->prepareHandler()->setTelegramInfo(
            \Yii::createObject(
                [
                    'class' => UserInfoSetData::class,
                    'telegram_id' => $request->telegramId,
                ]
            )
        );
    }

    public function actionSaveCode()
    {
        $request = TelegramSaveCodeRequest::createRequest();
        if (empty($request->telegramId)) {
            throw new BadRequestHttpException("TelegramID РЅРµ РјРѕР¶РµС‚ Р±С‹С‚СЊ РїСѓСЃС‚С‹Рј");
        }
        if (empty($request->code)) {
            throw new BadRequestHttpException("Code РЅРµ РјРѕР¶РµС‚ Р±С‹С‚СЊ РїСѓСЃС‚С‹Рј");
        }
        try {
            $this->prepareHandler()->confirmTelegramId(
                \Yii::createObject(
                    [
                        'class' => UserInfoSetData::class,
                        'telegram_id' => $request->telegramId,
                        'confirmCode' => $request->code,
                    ]
                )
            );
        } catch (TelegramWrongConfirmCodeException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return UserConnectionInfoHandler
     */
    private function prepareHandler()
    {
        return \Yii::$app->telegram->getUserConnectionInfoHandler()->setUserId(\Yii::$app->user->id);
    }
}
