<?php

namespace App\Utils;

class AlexaService extends BackendAbstract
{
    const SOURCE = 'ALEXA';

    public function sendCommand(
        string $deviceName,
        string $featureName,
        string $commandName,
        string $referenceId
    ): string {
        $status = $this->backendClient->sendCommand(
            $deviceName,
            $featureName,
            $commandName,
            $referenceId,
            self:: SOURCE
        );
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
