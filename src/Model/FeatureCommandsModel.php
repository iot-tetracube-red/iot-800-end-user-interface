<?php

namespace App\Model;

class FeatureCommandsModel {

	private $deviceName;

	private $featureName;

	/**
	 * @var CommandModel[]
	 */
	private $commands;


	/**
	 * FeatureCommandsModel constructor.
	 *
	 * @param array $response
	 */
	public function __construct( array $response ) {

	}

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
	 * @return CommandModel[]
	 */
	public function getCommands(): array {
		return $this->commands;
	}

	/**
	 * @param CommandModel[] $commands
	 */
	public function setCommands( array $commands ): void {
		$this->commands = $commands;
	}


}
