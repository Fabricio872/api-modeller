<?php

namespace Tests;

use Fabricio872\ApiModeller\ClientAdapter\ClientInterface;

class TestClientEmpty implements ClientInterface
{

    public function request(string $method, string $endpoint, array $options): string
    {
        return json_encode([
            "method" => $method,
            "endpoint" => $endpoint,
            "options" => $options,
            "subClass" => []
        ]);
    }
}