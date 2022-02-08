<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller;

class Repo
{
    /**
     * @var string
     */
    private $model;

    /**
     * @var string|null
     */
    private $identifier = '';

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $options = [];

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

    /**
     * @return string|null
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string|null $identifier
     * @return $this
     */
    public function setIdentifier(
        $identifier
    ): self {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Parameters documentation

     * @return array|null
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array|null $parameters
     * @return $this
     */
    public function setParameters(
        $parameters
    ): self {
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

    public function addOptions(array $options): self
    {
        $this->options = array_replace_recursive($this->options, $options);
        return $this;
    }
}
