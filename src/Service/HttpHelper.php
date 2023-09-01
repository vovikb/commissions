<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpHelper
{
    private HttpClientInterface $baseClient;
    private HttpClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $client,
        LoggerInterface $logger
    ) {
        $this->baseClient = $client;
        $this->logger = $logger;
    }

    private function createClientWithOptions(string $baseUrl, array $headers = []): void
    {
        $options = [
            'base_uri' => $baseUrl,
        ];
        if ($headers) {
            $options['headers'] = $headers;
        }

        $this->client = (clone $this->baseClient)->withOptions($options);
    }

    public function sendRequest(string $method, string $baseUrl, string $endpoint, array $headers = [], array $options = []): ?string
    {
        $this->createClientWithOptions($baseUrl, $headers);

        try {
            $response = $this->client->request($method, $endpoint, $options);
        } catch (TransportExceptionInterface | ClientException | RedirectionException | ServerException $ex) {

            $this->logger->error($ex->getMessage());

            return null;
        }

        if ($response->getStatusCode() >= 400) {
            $msg = sprintf('Error fetching %s %s%s: status code is %d.',
                $method, $baseUrl, $endpoint, $response->getStatusCode());

            $this->logger->error($msg);

            return null;
        }

        return $response->getContent();
    }

}