<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 20.02.18
 * Time: 18:18
 */

namespace Zvinger\Telegram\command;

use Zvinger\Telegram\command\BaseCommand;
use Zvinger\Telegram\handlers\message\TelegramMessageHandler;

class HelpCommand extends \Telegram\Bot\Commands\HelpCommand
{
    public $description = 'Commands List';

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        $commands = $this->telegram->getCommands();

        $text = '';
        foreach ($commands as $name => $handler) {
            $argsList = '';
            if ($handler instanceof BaseCommand) {
                $args = $handler->getAvailableArguments();
                if ($args) {
                    $argsList = implode(" ", $args);
                }
                if ($argsList) {
                    $argsList = ' _' . $argsList . '_';
                }
            }
            $text .= sprintf('/%s%s - %s' . PHP_EOL, $name, $argsList, $handler->getDescription());
        }
        $use_sendMessage_parameters = compact('text');
        $use_sendMessage_parameters['parse_mode'] = TelegramMessageHandler::PARSE_MARKDOWN;
        $this->replyWithMessage($use_sendMessage_parameters);
    }
}