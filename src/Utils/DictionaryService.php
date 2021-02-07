<?php

namespace App\Utils;

use App\Model\CommandModel;

class DictionaryService {

	const COMMANDS = [
		'TURN_ON'  => '🌟 Accendi',
		'TURN_OFF' => '🌑 Spegni',
		'OPEN'     => '🌲 Apri',
		'CLOSE'    => '🍁 Chiudi',
		'READ'     => '⚡️ Status',
	];

	const COMMAND_DONE = [
		'TURN_ON'  => [
			0 => '🌑 Spento',
			1 => '🌟 Acceso',
		],
		'TURN_OFF' => [
			0 => '🌑 Spento',
			1 => '🌟 Acceso',
		],
		'OPEN'     => [
			'0' => '🍁 Chiuso',
            '0.25' => '🌘 In chiusura',
            '0.75' => '🌅 In apertura',
			'1' => '🌲 Aperto',
		],
		'CLOSE'    => [
            '0' => '🍁 Chiuso',
            '0.25' => '🌘 In chiusura',
            '0.75' => '🌅 In apertura',
            '1' => '🌲 Aperto',
		],
		'READ'     => '⚡️ Status: ',
	];

	const STATUS = [
		'SWITCH' => [
			0 => '🌑 Spento',
			1 => '🌟 Acceso',
		],
	];

	/**
	 * @param CommandModel $command
	 *
	 * @return string
	 */
	public function getCommandLabel( CommandModel $command ): string {
		return self::COMMANDS[ $command->getName() ] ?? $command->getName();
	}

	public function getCommandDoneLabel( $command, $value ) {
		$answer = '👌 Fatto!';
		if ( isset( self::COMMAND_DONE[ $command ] ) && is_array( self::COMMAND_DONE[ $command ] ) && isset( self::COMMAND_DONE[ $command ][ (int) $value ] ) ) {
			$answer = self::COMMAND_DONE[ $command ][ (int) $value ];
		}

		if ( isset( self::COMMAND_DONE[ $command ] ) && ! is_array( self::COMMAND_DONE[ $command ] ) ) {
			$answer = self::COMMAND_DONE[ $command ] . $value;
		}

		return $answer;
	}

	public function getFeatureStatus( $featureType, $value ) {
		$answer = '⚡️ Status: ' . $value;
		if ( isset( self::STATUS[ $featureType ] ) && isset( self::STATUS[ $featureType ][ $value ] ) ) {
			$answer = self::STATUS[ $featureType ][ $value ];
		}

		return $answer;
	}

	public function getCommandDone(): string {
	    return '👌 Fatto!';
    }

    public function getNotExistingDevice(): string {
	    return '🥶 Questo device non esiste...';
    }

    public function getUnavailableService(): string
    {
        return '😱 Servizio non disponibile!';
    }

    public function getClientError(): string
    {
        return '🤓 Mi hai inviato dei dati sbagliati';
    }

    public function getGenericError(): string {
	    return '♠️ Ops! È successo qualcosa di inatteso';
    }

}
