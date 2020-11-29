<?php

namespace App\EventSubscriber;

use App\Controller\AlexaValidatedController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Tightenco\Collect\Support\Collection;

class AlexaValidation implements EventSubscriberInterface {

	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var ParameterBagInterface
	 */
	private $parameterBag;

	public function __construct(LoggerInterface $logger, ParameterBagInterface $parameterBag) {
		$this->logger = $logger;
		$this->parameterBag = $parameterBag;
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::CONTROLLER => [
				['onController', 10],
			],

		];
	}

	public function onController( ControllerEvent $event ) {
		$controller = $event->getController();

		// when a controller class defines multiple action methods, the controller
		// is returned as [$controllerInstance, 'methodName']
		if (is_array($controller)) {
			$controller = $controller[0];
		}

		$validationEnabled = true;

		try {
			$validationEnabled = $this->parameterBag->get('ALEXA_ENABLE_VALIDATION');
		} catch (ParameterNotFoundException $e) {
			$this->logger->warning('Alexa validation is not explicitly configured, assumed ENABLED');
		}

		if ( ! $controller instanceof AlexaValidatedController || ! $validationEnabled) {
			return;
		}

		$request = $event->getRequest();

		try {
			$this->validateHeaders($request);
		} catch (\Exception $e) {
			$this->logger->error('Error in headers validation: ' . $e->getMessage());
			throw new BadRequestHttpException('');
		}

		try {
			$this->validateCertificate($request);
		} catch (\Exception $e) {
			$this->logger->error('Error in certification validation: ' . $e->getMessage());
			throw new BadRequestHttpException('');
		}

		try {
			$this->validateTimestamp($request);
		} catch (\Exception $e) {
			$this->logger->error('Error in timestamp validation: ' . $e->getMessage());
			throw new BadRequestHttpException('');
		}

		try {
			$this->validateSkillId($request);
		} catch (\Exception $e) {
			$this->logger->error('Error in skill id validation: ' . $e->getMessage());
			throw new BadRequestHttpException('');
		}

		return;
	}

	/**
	 * Validate the certificate headers
	 *
	 * @param Request $request
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function validateHeaders(Request $request) {
		$headers = $request->headers;
		$chainUrl = $headers->get('signaturecertchainurl');
		if ( ! isset( $chainUrl ) ) {
			throw new \Exception('This request did not come from Amazon.');
		}
		if (is_array($chainUrl)) {
			$chainUrl = $chainUrl[0];
		}

		$uriParts = parse_url( $chainUrl );
		if ( strcasecmp( $uriParts['host'], 's3.amazonaws.com' ) !== 0 ) {
			throw new \Exception('The host for the Certificate provided in the header is invalid' );
		}
		if ( strpos( $uriParts['path'], '/echo.api/' ) !== 0 ) {
			throw new \Exception('The URL path for the Certificate provided in the header is invalid' );
		}
		if ( strcasecmp( $uriParts['scheme'], 'https' ) !== 0 ) {
			throw new \Exception('The URL is using an unsupported scheme. Should be https' );
		}
		if ( array_key_exists( 'port', $uriParts ) && '443' !== $uriParts['port'] ) {
			throw new \Exception('The URL is using an unsupported https port' );
		}

		return true;
	}

	/**
	 * Validate the certificate
	 *
	 * @param Request $request
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function validateCertificate(Request $request) {
		$headers = $request->headers;
		$chainUrl = $headers->get('signaturecertchainurl');
		$signature = $headers->get('signature');
		$echoDomain = 'echo-api.amazon.com';
		if (is_array($chainUrl)) {
			$chainUrl = $chainUrl[0];
		}
		if (is_array($signature)) {
			$signature = $signature[0];
		}
		$pem = file_get_contents( $chainUrl );
		// Validate certificate chain and signature.
		$ssl_check = openssl_verify( $request->getContent(), base64_decode( $signature ), $pem, 'sha1' );
		if ( intval( $ssl_check ) !== 1 ) {
			throw new \Exception( openssl_error_string() );
		}
		// Parse certificate for validations below.
		$parsed_certificate = openssl_x509_parse( $pem );
		if ( ! $parsed_certificate ) {
			throw new \Exception('x509 parsing failed' );
		}
		// Check that the domain echo-api.amazon.com is present in
		// the Subject Alternative Names (SANs) section of the signing certificate.
		if ( strpos( $parsed_certificate['extensions']['subjectAltName'], $echoDomain ) === false ) {
			throw new \Exception( 'subjectAltName Check Failed' );
		}
		// Check that the signing certificate has not expired
		// (examine both the Not Before and Not After dates).
		$valid_from = $parsed_certificate['validFrom_time_t'];
		$valid_to   = $parsed_certificate['validTo_time_t'];
		$time       = time();
		if ( ! ( $valid_from <= $time && $time <= $valid_to ) ) {
			throw new \Exception( 'certificate expiration check failed' );
		}

		return true;
	}

	/**
	 * Validate the request timestamp
	 *
	 * @param Request $request
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function validateTimestamp(Request $request) {
		$body = Collection::make((array) json_decode($request->getContent(), true));
		$request = $body->get('request');
		if ( time() - strtotime( $request['timestamp'] ) > 60 ) {
			throw new \Exception('Timestamp validation failure. Current time: ' . time() . ' vs. Timestamp: ' . $request['timestamp']);
		}
		return true;
	}

	/**
	 * Validate the skill id given by the request
	 *
	 * @param Request $request
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function validateSkillId(Request $request) {
		$body = Collection::make((array) json_decode($request->getContent(), true));
		$skillId = $body->get('session')['application']['applicationId'];
		try {
			$configuredSkillId = $this->parameterBag->get('SKILL_ID');
			if ( $configuredSkillId !== $skillId) {
				throw new \Exception('Skill ID is not valid');
			}
		} catch (ParameterNotFoundException $e) {
			throw new \Exception($e->getMessage());
		}

		return true;
	}
}
