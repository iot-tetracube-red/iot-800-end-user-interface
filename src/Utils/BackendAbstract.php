<?php

namespace App\Utils;

use App\Client\BackendClient;

abstract class BackendAbstract
{

    /**
     * @var BackendClient
     */
    protected $backendClient;
    /**
     * @var DictionaryService
     */
    protected $dictionaryService;

    public function __construct(BackendClient $backendClient, DictionaryService $dictionaryService)
    {
        $this->backendClient = $backendClient;
        $this->dictionaryService = $dictionaryService;
    }

    abstract function sendCommand(
        string $deviceName,
        string $featureName,
        string $commandName,
        string $referenceId
    ): string;

}
