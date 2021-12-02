<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use Fabricio872\ApiModeller\Annotations\Resource;
use Fabricio872\ApiModeller\Annotations\ResourceInterface;
use Fabricio872\ApiModeller\Annotations\Resources;
use Fabricio872\ApiModeller\Annotations\SubModel;
use Fabricio872\ApiModeller\ClientAdapter\ClientInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Source;

class Modeller
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var Repo
     */
    private $repo;

    public function __construct(Reader $reader, ClientInterface $client, Environment $twig)
    {
        $this->reader = $reader;
        $this->client = $client;
        $this->twig = $twig;
    }

    /**
     * @return string
     */
    public function getRawData()
    {
        return $this->client->request($this->getMethod(), $this->getEndpoint(), $this->getOptions());
    }

    public function setRepo(Repo $repo): self
    {
        $this->repo = $repo;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->getAnnotation()
            ->method;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return self::renderEndpoint($this->getAnnotation(), $this->repo);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return array_merge_recursive($this->getAnnotation()->options, $this->repo->getOptions());
    }

    /**
     * @return ArrayCollection|mixed
     */
    public function getData()
    {
        $normalizedContent = self::getSerializer()->decode((string) $this->getRawData(), $this->getAnnotation()->type);

        if ($normalizedContent === null) {
            return new ArrayCollection();
        }
        return $this->modelBuilder($normalizedContent, $this->repo->getModel());
    }

    /**
     * @return resource
     */
    private function getAnnotation()
    {
        return $this->getResource($this->repo->getModel(), $this->repo->getIdentifier());
    }

    /**
     * @return array|ArrayCollection|object
     */
    private function modelBuilder(array $normalizedData, string $model)
    {
        if (array_values($normalizedData) === $normalizedData) {
            $return = new ArrayCollection();
            foreach ($normalizedData as $normalizedItem) {
                $return->add($this->subModelBuilder(self::getSerializer()->denormalize($normalizedItem, $model)));
            }
            return $return;
        }
        return $this->subModelBuilder(self::getSerializer()->denormalize($normalizedData, $model));
    }

    /**
     * @param array|object $denormalized
     * @return array|object
     */
    private function subModelBuilder($denormalized)
    {
        $reflectionClass = new \ReflectionClass($denormalized);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $subModel = $this->reader->getPropertyAnnotation($reflectionProperty, SubModel::class);
            if ($subModel instanceof SubModel) {
                $reflectionProperty->setAccessible(true);
                if ($reflectionProperty->getValue($denormalized) !== null) {
                    $reflectionProperty->setValue(
                        $denormalized,
                        $this->modelBuilder($reflectionProperty->getValue($denormalized), $subModel->model)
                    );
                }
            }
        }
        return $denormalized;
    }

    /**
     * @return Serializer
     */
    private static function getSerializer()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        return new Serializer($normalizers, $encoders);
    }

    private function renderEndpoint(Resource $annotation, Repo $repo): string
    {
        try {
            $template = $this->twig->createTemplate($annotation->endpoint);
            $rendered = $this->twig->render($template, $repo->getParameters());
        } catch (RuntimeError $exception) {
            $exception->setSourceContext(new Source('', $repo->getModel()));
            throw $exception;
        }
        return $rendered;
    }

    private function getResource(string $model, string $identifier): Resource
    {
        $reflection = new \ReflectionClass($model);

        $resourceInterface = $this->reader->getClassAnnotation($reflection, ResourceInterface::class);
        if ($resourceInterface instanceof Resources) {
            if (! array_key_exists($identifier, $resourceInterface->resources)) {
                throw new \Exception(sprintf('Identifier: "%s" does not exists in Model: "%s"', $identifier, $model));
            }
            return $resourceInterface->resources[$identifier];
        }
        return $resourceInterface;
    }
}
