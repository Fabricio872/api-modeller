<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller\ClientAdapter;

use GuzzleHttp\Client;

class Guzzle implements ClientInterface
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request(string $method, string $endpoint, array $options): string
    {
        $method = strtolower($method);
        return $this->client->$method($endpoint, $options)->getBody()->getContents();
    }
}
