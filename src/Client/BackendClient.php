<?php

namespace App\Client;

use App\Entity\Home;
use App\Model\FeatureCommandsModel;
use App\Model\DeviceFeatureModel;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BackendClient
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $endpoint;

    private $serializer;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entity_manager,
        SerializerInterface $serializer
    ) {
        $home = $entity_manager->getRepository(Home::class);
        $smart_home = $home->findOneBy([]);
        if (is_null($smart_home)) {
            $logger->error('There is no Home ip into database, aborting!');
            throw new \Exception('There is no Home ip into database, aborting!');
        }
        $this->endpoint = 'http://'.$smart_home->getIp().':8080/bot';
        $this->logger = $logger;
        $this->serializer = new $serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    /**
     * Get the list of the features with their device name
     *
     * @return DeviceFeatureModel[]
     */
    public function getFeatures(): array
    {
        $url = '/features';
        $payload = [];
        $result = $this->get($url, $payload);

        try {
            $body = $result->getContent();
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $this->logger->error('Error from backend '.$e->getMessage());

            return [];
        }

        $appliancesResponse = json_decode($body, true);
        $featuresList = [];
        foreach ($appliancesResponse as $feature) {
            $featuresList[] = $this->serializer->deserialize(json_encode($feature), DeviceFeatureModel::class, 'json');
        }

        $this->logger->info($body);


        return $featuresList;
    }

    /**
     * Get the commands available for a feature
     *
     * @param string $deviceName
     * @param string $featureName The feature name.
     *
     * @return FeatureCommandsModel
     */
    public function getCommands(string $deviceName, string $featureName): ?FeatureCommandsModel
    {
        $url = '/devices/'.$deviceName.'/features/'.$featureName.'/commands';

        $result = $this->get($url);

        try {
            $body = $result->getContent();
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $this->logger->error('Error from backend '.$e->getMessage());

            return null;
        }
        $this->logger->info($body);

        return $this->serializer->deserialize($body, FeatureCommandsModel::class, 'json');
    }

    public function sendCommand(
        string $deviceName,
        string $featureName,
        string $commandName,
        string $referenceId,
        string $source
    ): int {
        $url = '/devices/features/command';
        $payload = [
            'deviceName' => $deviceName,
            'featureName' => $featureName,
            'commandName' => $commandName,
            'referenceId' => $referenceId,
            'source' => $source,
        ];

        $response = $this->patch($url, $payload);

        try {
            $status = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Error from backend '.$e->getMessage());

            return 500;
        }
        $this->logger->info($status);

        return $status;
    }

    private function post(string $url, array $params = []): ?ResponseInterface
    {
        $client = HttpClient::create();
        $this->logger->info('POST request to '.$this->endpoint.$url.' with '.serialize($params));
        try {
            $result = $client->request(
                'POST',
                $this->endpoint.$url,
                [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'timeout' => 6,
                    'json' => $params,
                ]
            );
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Error in backend service POST: '.$e->getMessage());

            return null;
        }

        return $result;
    }

    private function patch(string $url, array $params = []): ?ResponseInterface
    {
        $client = HttpClient::create();
        $this->logger->info('POST request to '.$this->endpoint.$url.' with '.serialize($params));
        try {
            $result = $client->request(
                'PATCH',
                $this->endpoint.$url,
                [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'timeout' => 6,
                    'json' => $params,
                ]
            );
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Error in backend service POST: '.$e->getMessage());

            return null;
        }

        return $result;
    }

    private function get(string $url, array $params = []): ?ResponseInterface
    {
        $client = HttpClient::create();
        try {
            $result = $client->request(
                'GET',
                $this->endpoint.$url,
                [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'query' => $params,
                    'timeout' => 6,
                ]
            );
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Error in backend service GET: '.$e->getMessage());

            return null;
        }

        return $result;
    }
}
