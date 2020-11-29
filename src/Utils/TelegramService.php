<?php

namespace App\Utils;

use App\Model\FeatureCommandsModel;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class TelegramService extends BackendAbstract
{

    public function featuresKeyboard()
    {
        $features = $this->backendClient->getFeatures();
        if (empty($features)) {
            return new ReplyKeyboardMarkup([[]]);
        }

        $buttons = [];
        foreach ($features as $feature) {
            $buttons[] = ['🔸 '.$feature['name']];
        }
        $keyboard = new ReplyKeyboardMarkup($buttons);
        $keyboard->setOneTimeKeyboard(false);

        return $keyboard;
    }

    /**
     * @param $featureName
     *
     * @return FeatureCommandsModel|null
     */
    public function getFeature($featureName)
    {
        return $this->backendClient->getCommands($featureName);
    }

    public function commandKeyboard($featureName)
    {
        $commands = $this->backendClient->getCommands($featureName);
        $buttons = [];
        $tmp = [];
        foreach ($commands->getCommands() as $command) {
            $commandLabel = $this->dictionaryService->getCommandLabel($command);
            $tmp[] =
                [
                    'text' => $commandLabel,
                    'callback_data' => $featureName.' - '.$command,
                ];
        }
        $buttons[] = $tmp;

        return new InlineKeyboardMarkup($buttons);
    }

}
