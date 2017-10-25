<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.10.17
 * Time: 14:24
 */

namespace Zvinger\Telegram\console\command;

use yii\console\Controller;
use yii\helpers\Console;
use Zvinger\Telegram\components\TelegramComponent;

class TelegramConsoleController extends Controller
{
    /**
     * @var TelegramComponent
     */
    private $_telegramComponent;

    public function actionLongPolling($timeout = 30)
    {
        $telegramComponent = $this->getTelegramComponent();
        $telegramClient = \Yii::$app->telegramComponent->getTelegramClient();
        Console::output("Started long polling listener");
        while (TRUE) {
            $telegramLastUpdate = $telegramComponent->getLastUpdateId();
            $params = [
                'offset'  => ++$telegramLastUpdate,
                'timeout' => $timeout,
            ];
            $updates = $telegramClient->getUpdates($params);
            foreach ($updates as $update) {
                Console::stdout("Handling update #" . $update->getUpdateId() . PHP_EOL);
                $telegramComponent->getIncomingMessageHandler()->workLongPollingUpdate($update);
                $telegramComponent->setLastUpdateId($update->getUpdateId());
            }
        }
    }

    /**
     * @return TelegramComponent
     */
    private function getTelegramComponent(): TelegramComponent
    {
        return $this->_telegramComponent;
    }

    /**
     * @param TelegramComponent $telegramComponent
     */
    public function setTelegramComponent(TelegramComponent $telegramComponent)
    {
        $this->_telegramComponent = $telegramComponent;
    }


}