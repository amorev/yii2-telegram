<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 25.01.18
 * Time: 23:47
 */

namespace Zvinger\Telegram\api;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;

class TelegramApiClient extends Api
{
    public function editMessageText($data)
    {
        $response = $this->post('editMessageText', $data);

        return new Message($response->getDecodedBody());
    }

    public function commandsHandler($webhook = FALSE, $timeout = NULL)
    {
        if ($webhook) {
            $update = $this->getWebhookUpdates();
            $this->processCommand($update);

            return $update;
        }

        $updates = $this->getUpdates([
            'timeout' => $timeout
        ]);
        $highestId = -1;

        foreach ($updates as $update) {
            $highestId = $update->getUpdateId();
            $this->processCommand($update);
        }

        //An update is considered confirmed as soon as getUpdates is called with an offset higher than its update_id.
        if ($highestId != -1) {
            $params = [];
            $params['offset'] = $highestId + 1;
            $params['limit'] = 1;
            $this->getUpdates($params);
        }

        return $updates;
    }


}