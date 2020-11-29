<?php

namespace App\EventSubscriber;

use App\Controller\TelegramValidatedController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TelegramUserFilter implements EventSubscriberInterface {

	private $allowedUsers = [
		'BotKreator',
		'DaviCube',
		'SmartIglooBot',
	];

	public static function getSubscribedEvents() {
		return [
			KernelEvents::CONTROLLER_ARGUMENTS => [
				[ 'onController', 10 ],
			],

		];
	}

	public function onController( ControllerArgumentsEvent $event ) {
		$controller = $event->getController();

		// when a controller class defines multiple action methods, the controller
		// is returned as [$controllerInstance, 'methodName']
		if ( is_array( $controller ) ) {
			$controller = $controller[0];
		}

		if ( ! $controller instanceof TelegramValidatedController ) {
			return;
		}

		$request = $event->getRequest();

		$body = json_decode( $request->getContent(), true );
		$user = '';
		if ( isset( $body['message'] ) ) {
			$user = $body['message']['from']['username'];
		} elseif ( isset( $body['callback_query'] ) ) {
			$user = $body['callback_query']['from']['username'];
		}
		$arguments = $event->getArguments();
		if ( in_array( $user, $this->allowedUsers ) ) {
			foreach ( $arguments as $key => $argument ) {
				if ( is_bool( $argument ) ) {
					$arguments[ $key ] = true;
				}
			}
		}
		$event->setArguments( $arguments );
	}
}
