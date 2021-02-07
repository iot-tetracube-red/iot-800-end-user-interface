<?php

namespace App\Model;

class FeatureCommandsModel
{

    private $deviceName;

    private $featureName;

    private $commands;

    /**
     * @return string
     */
    public function getDeviceName(): string
    {
        return $this->deviceName;
    }

    /**
     * @param string $deviceName
     */
    public function setDeviceName(string $deviceName): void
    {
        $this->deviceName = $deviceName;
    }

    /**
     * @return string
     */
    public function getFeatureName(): string
    {
        return $this->featureName;
    }

    /**
     * @param string $featureName
     */
    public function setFeatureName(string $featureName): void
    {
        $this->featureName = $featureName;
    }

    /**
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @param array $commands
     */
    public function setCommands(array $commands): void
    {
        $this->commands = $commands;
    }

}
