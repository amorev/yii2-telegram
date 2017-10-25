<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.10.17
 * Time: 9:28
 */

namespace Zvinger\Telegram\handlers\incoming;

class IncomingMessageHandler
{
    public function workLongPolling()
    {
        $telegramClient = \Yii::$app->telegram->getTelegramClient();
        while (TRUE) {
            $telegramLastUpdate = \Yii::$app->telegram->getLastUpdateId();
            $params = [
                'offset'  => ++$telegramLastUpdate,
                'timeout' => $timeout,
            ];
            $updates = $telegramClient->getUpdates($params);
            foreach ($updates as $update) {
                Console::stdout("Handling update #" . $update->getUpdateId() . PHP_EOL);
                \Yii::$app->bot_telegram->handleUpdate($update);
            }
        }
    }
}