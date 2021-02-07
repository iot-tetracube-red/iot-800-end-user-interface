<?php

namespace App\Utils;

use App\Model\FeatureCommandsModel;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class TelegramService extends BackendAbstract
{

    const SOURCE = 'TELEGRAM';

    public function featuresKeyboard(): ReplyKeyboardMarkup
    {
        $features = $this->backendClient->getFeatures();
        if (empty($features)) {
            return new ReplyKeyboardMarkup([[]]);
        }

        $buttons = [];
        foreach ($features as $feature) {
            $buttons[] = ['🔸 '.$feature->getDeviceName().' - '.$feature->getFeatureName()];
        }
        $keyboard = new ReplyKeyboardMarkup($buttons);
        $keyboard->setOneTimeKeyboard(false);

        return $keyboard;
    }

    public function getFeature(string $deviceName, string $featureName): ?FeatureCommandsModel
    {
        return $this->backendClient->getCommands($deviceName, $featureName);
    }

    public function commandKeyboard(string $deviceName, string $featureName): InlineKeyboardMarkup
    {
        $featureCommands = $this->backendClient->getCommands($deviceName, $featureName);
        $buttons = [];
        if ($featureCommands instanceof FeatureCommandsModel) {
            $tmp = [];
            foreach ($featureCommands->getCommands() as $command) {
                $commandLabel = $this->dictionaryService->getCommandLabel($command);
                $tmp[] =
                    [
                        'text' => $commandLabel,
                        'callback_data' => $deviceName.' - '.$featureName.' - '.$command,
                    ];
            }
            $buttons[] = $tmp;
        }

        return new InlineKeyboardMarkup($buttons);
    }

    public function sendCommand(
        string $deviceName,
        string $featureName,
        string $commandName,
        string $referenceId
    ): string
    {
        $status = $this->backendClient->sendCommand($deviceName, $featureName, $commandName, $referenceId, self:: SOURCE);
        switch ($status) {
            case 204:
                return $this->dictionaryService->getCommandDone();
                break;
            case 404:
                return $this->dictionaryService->getNotExistingDevice();
                break;
            case 400:
                return $this->dictionaryService->getClientError();
                break;
            case 503:
                return $this->dictionaryService->getUnavailableService();
                break;
            default:
                return $this->dictionaryService->getGenericError();
        }

    }

}
