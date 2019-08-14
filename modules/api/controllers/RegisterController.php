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
        return [
            'username' => '@amorev',
            'telegramId' => 51817529,
        ];
    }

    public function actionInitRegister()
    {
        $request = TelegramInitRegisterRequest::createRequest();
        if (empty($request->telegramId)) {
            throw new BadRequestHttpException("TelegramID не может быть пустым");
        }
        \Yii::$app->telegram->message($request->telegramId, 'Your code is 123123');
    }

    public function actionSaveCode()
    {
        $request = TelegramSaveCodeRequest::createRequest();
        if (empty($request->telegramId)) {
            throw new BadRequestHttpException("TelegramID не может быть пустым");
        }
        if (empty($request->code)) {
            throw new BadRequestHttpException("Code не может быть пустым");
        }
        if ($request->code != 123123) {
            throw new BadRequestHttpException("Код неверен");
        }
    }
}
