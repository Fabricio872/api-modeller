<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller\Annotations;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS"})
 */
class ModelTitle
{
    /**
     * @var array
     * @Required()
     */
    public $title = [];
}
