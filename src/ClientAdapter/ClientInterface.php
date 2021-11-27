<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller\ClientAdapter;

interface ClientInterface
{
    public function request(string $method, string $endpoint, array $options): ?string;
}
