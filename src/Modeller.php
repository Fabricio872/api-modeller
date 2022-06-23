<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Fabricio872\ApiModeller\Annotations\ModelTitle;
use Fabricio872\ApiModeller\Annotations\Resource;
use Fabricio872\ApiModeller\Annotations\ResourceInterface;
use Fabricio872\ApiModeller\Annotations\Resources;
use Fabricio872\ApiModeller\Annotations\SubModel;
use Fabricio872\ApiModeller\ClientAdapter\ClientInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
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

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(Reader $reader, ClientInterface $client, Environment $twig)
    {
        $this->reader = $reader;
        $this->client = $client;
        $this->twig = $twig;
        $this->serializer = new Serializer([new ObjectNormalizer()], [
            new JsonEncoder(),
            new XmlEncoder(),
            new CsvEncoder(),
        ]);
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
     * @return ArrayCollection
     */
    public function getData()
    {
        $normalizedContent = $this->serializer->decode((string) $this->getRawData(), $this->getAnnotation()->type);

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
        $reflectionClass = new \ReflectionClass($model);
        $modelTitles = $this->reader->getClassAnnotation($reflectionClass, ModelTitle::class);
        if ($modelTitles !== null) {
            $this->shiftData($normalizedData, $modelTitles->title);
        }

        if ($normalizedData === null) {
            return null;
        }

        if (array_values($normalizedData) === $normalizedData) {
            $return = new ArrayCollection();
            foreach ($normalizedData as $normalizedItem) {
                $return->add($this->subModelBuilder($this->serializer->denormalize($normalizedItem, $model)));
            }
            return $return;
        }
        return $this->subModelBuilder($this->serializer->denormalize($normalizedData, $model));
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

    private function shiftData(array & $data, array $titleNest)
    {
        if (is_array($titleNest)) {
            foreach ($titleNest as $titles) {
                if (! is_array($titles)) {
                    $titles = [$titles];
                }

                foreach ($titles as $title) {
                    if (isset($data[$title])) {
                        $data = $data[$title];
                    }
                }
            }
        }
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
        if ($resourceInterface === null && $reflection->getParentClass()) {
            return $this->getResource($reflection->getParentClass()->getName(), $identifier);
        }
        if ($resourceInterface instanceof Resources) {
            if (! array_key_exists($identifier, $resourceInterface->resources)) {
                throw new Exception(sprintf('Identifier: "%s" does not exists in Model: "%s"', $identifier, $model));
            }
            return $resourceInterface->resources[$identifier];
        }
        return $resourceInterface;
    }
}
