<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller\ClientAdapter;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Symfony implements ClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function request(string $method, string $endpoint, array $options): string
    {
        return $this->client->request($method, $endpoint, $options)
            ->getContent();
    }
}
