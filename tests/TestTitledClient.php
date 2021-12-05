<?php

namespace Tests;

use Fabricio872\ApiModeller\ClientAdapter\ClientInterface;

class TestTitledClient implements ClientInterface
{

    public function request(string $method, string $endpoint, array $options): string
    {
        return json_encode([
            "testTitle" => [
                "method" => $method,
                "endpoint" => $endpoint,
                "options" => $options,
                "subClass" => [
                    "sub1" => true,
                    "sub2" => 420,
                    "sub3" => "test"
                ]
            ]
        ]);
    }
}