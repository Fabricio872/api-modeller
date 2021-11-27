<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller;

class Repo
{
    private string $model;

    private ?string $identifier = '';

    private array $parameters = [];

    private array $options = [];

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public static function new(string $model)
    {
        return new self($model);
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Parameters documentation
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(?array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }
}
