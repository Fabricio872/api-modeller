<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller\Annotations;

/**
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
class SubModel
{
    /**
     * Model to map array value
     *
     * @var string
     */
    public $model;
}