<?php

namespace App\Model;

class DeviceFeatureModel {

    private $deviceName;

    private $featureName;

    /**
     * @return mixed
     */
    public function getDeviceName()
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

}
