<?php

namespace App\Utils;

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
	 * @param $command
	 *
	 * @return string|null
	 */
	public function getCommandLabel( $command ) {
		return self::COMMANDS[ $command ] ?? '';
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


}
