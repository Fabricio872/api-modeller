<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller\Annotations;

use Doctrine\Common\Annotations\Annotation\Attribute;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes(
 *     @Attribute("resource", type="array")
 * )
 */
final class Resources implements ResourceInterface
{
    /** @var array */
    public $resources;

    public function __construct(array $resources = [])
    {
        $this->resources = $resources;
    }
}
