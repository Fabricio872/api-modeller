<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller\Annotations;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use phpDocumentor\Reflection\Types\ClassString;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"METHOD","PROPERTY"})
 */
class SubModel
{
    /**
     * @var ClassString
     */
    public $model;

    /**
     * Model to map array value
     */
    public function __construct(string $model)
    {
        $this->model = $model;
    }
}
