<?php

namespace App\Model;

class FeatureCommandsModel {

	/**
	 * @var integer
	 */
	private $status;

	/**
	 * @var array
	 */
	private $commands;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * FeatureCommandsModel constructor.
	 *
	 * @param array $response
	 *
	 * @throws \Exception
	 */
	public function __construct( array $response ) {
		if ( ! isset( $response['status'] ) || ! isset( $response['commands'] ) || ! isset( $response['type'] ) ) {
			throw new \Exception( 'Model error: the backend has returned an incorrect response' );
		}
		$this->status   = $response['status'];
		$this->commands = $response['commands'];
		$this->type     = $response['type'];
	}

	/**
	 * @return int
	 */
	public function getStatus(): int {
		return $this->status;
	}

	/**
	 * @param int $status
	 */
	public function setStatus( int $status ): void {
		$this->status = $status;
	}

	/**
	 * @return array
	 */
	public function getCommands(): array {
		return $this->commands;
	}

	/**
	 * @param array $commands
	 */
	public function setCommands( array $commands ): void {
		$this->commands = $commands;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType( string $type ): void {
		$this->type = $type;
	}
}
