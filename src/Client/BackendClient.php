<?php

namespace App\Client;

use App\Entity\Home;
use App\Model\FeatureCommandsModel;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class BackendClient {

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	private $endpoint;

	public function __construct( LoggerInterface $logger, EntityManagerInterface $entity_manager ) {
		$home       = $entity_manager->getRepository( Home::class );
		$smart_home = $home->findOneBy([]);
		if ( is_null( $smart_home ) ) {
			$logger->error( 'There is no Home ip into database, aborting!' );
			throw new \Exception( 'There is no Home ip into database, aborting!' );
		}
		$this->endpoint = 'http://' . $smart_home->getIp() . ':8090';
		$this->logger   = $logger;
	}

	public function getFeatures() {
		$url     = '/appliances';
		$payload = [];
		$result  = $this->get( $url, $payload );

		try {
			$body = $result->getContent();
		} catch ( ClientExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return [];
		} catch ( RedirectionExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return [];
		} catch ( ServerExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return [];
		} catch ( TransportExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return [];
		}

		$appliancesResponse = json_decode( $body, true );
		$this->logger->info( $body );


		return $appliancesResponse;
	}

	/**
	 * Get the commands available for a feature
	 *
	 * @param string $featureName The feature name.
	 *
	 * @return FeatureCommandsModel
	 */
	public function getCommands( $featureName ) {
		$url = '/appliances/' . $featureName . '/commands';

		$result = $this->get( $url );

		try {
			$body = $result->getContent();
		} catch ( ClientExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return null;
		} catch ( RedirectionExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return null;
		} catch ( ServerExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return null;
		} catch ( TransportExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return null;
		}
		$this->logger->info( $body );
		try {
			return new FeatureCommandsModel( json_decode( $body, true ) );
		} catch ( \Exception $e ) {
			$this->logger->error( 'Error from model: ' . $e->getMessage() );

			return null;
		}
	}

	public function sendCommand( $featureName, $command, &$value = null ) {
		$url     = '/appliances/command';
		$payload = [
			'command' => $command,
			'name'    => $featureName,
		];

		$response = $this->patch( $url, $payload );

		try {
			$body = $response->getContent();
		} catch ( ClientExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return false;
		} catch ( RedirectionExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return false;
		} catch ( ServerExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return false;
		} catch ( TransportExceptionInterface $e ) {
			$this->logger->error( 'Error from backend ' . $e->getMessage() );

			return false;
		}
		$this->logger->info( $body );
		$response = json_decode( $body, true );
		$value    = $response['currentStatus'] ?? null;

		return $response['success'] ?? false;
	}

	private function post( $url, $params = [] ) {
		$client = HttpClient::create();
		$this->logger->info( 'POST request to ' . $this->endpoint . $url . ' with ' . serialize( $params ) );
		try {
			$result = $client->request(
				'POST',
				$this->endpoint . $url,
				[
					'headers' => [
						'Accept' => 'application/json',
					],
					'timeout' => 6,
					'json'    => $params,
				]
			);
		} catch ( TransportExceptionInterface $e ) {
			$this->logger->error( 'Error in backend service POST: ' . $e->getMessage() );

			return [];
		}

		return $result;
	}

	private function patch( $url, $params = [] ) {
		$client = HttpClient::create();
		$this->logger->info( 'POST request to ' . $this->endpoint . $url . ' with ' . serialize( $params ) );
		try {
			$result = $client->request(
				'PATCH',
				$this->endpoint . $url,
				[
					'headers' => [
						'Accept' => 'application/json',
					],
					'timeout' => 6,
					'json'    => $params,
				]
			);
		} catch ( TransportExceptionInterface $e ) {
			$this->logger->error( 'Error in backend service POST: ' . $e->getMessage() );

			return [];
		}

		return $result;
	}

	private function get( $url, $params = [] ) {
		$client = HttpClient::create();
		try {
			$result = $client->request(
				'GET',
				$this->endpoint . $url,
				[
					'headers' => [
						'Accept' => 'application/json',
					],
					'query'   => $params,
					'timeout' => 6,
				]
			);
		} catch ( TransportExceptionInterface $e ) {
			$this->logger->error( 'Error in backend service GET: ' . $e->getMessage() );

			return [];
		}

		return $result;
	}
}
