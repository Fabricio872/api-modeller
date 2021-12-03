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

    /**
     * @param Reader $reader
     * @param ClientInterface $client
     * @param Environment $twig
     */
    public function __construct(Reader $reader, ClientInterface $client, Environment $twig)
    {
        $this->reader = $reader;
        $this->client = $client;
        $this->twig = $twig;
    }

    /**
     * @return string
     * @throws RuntimeError
     */
    public function getRawData()
    {
        return $this->client->request(
            $this->getMethod(),
            $this->getEndpoint(),
            $this->getOptions()
        );
    }

    /**
     * @param Repo $repo
     * @return Modeller
     */
    public function setRepo(Repo $repo): Modeller
    {
        $this->repo = $repo;
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getMethod()
    {
        return $this->getAnnotation()->method;
    }

    /**
     * @return string
     * @throws RuntimeError
     */
    public function getEndpoint()
    {
        return self::renderEndpoint($this->getAnnotation(), $this->repo);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getOptions()
    {
        return array_merge_recursive($this->getAnnotation()->options, $this->repo->getOptions());
    }

    /**
     * @return Resource
     * @throws \Exception
     */
    private function getAnnotation()
    {
        return $this->getResource($this->repo->getModel(), $this->repo->getIdentifier());
    }

    /**
     * @return ArrayCollection|mixed
     */
    public function getData()
    {
        $normalizedContent = self::getSerializer()->decode((string)$this->getRawData(), $this->getAnnotation()->type);

        if ($normalizedContent == null) {
            return new ArrayCollection();
        }
        return $this->modelBuilder($normalizedContent, $this->repo->getModel());
    }

    /**
     * @param array $normalizedData
     * @param string $model
     * @return array|ArrayCollection|object
     * @throws \ReflectionException
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
     * @throws \ReflectionException
     */
    private function subModelBuilder($denormalized)
    {
        $reflectionClass = new \ReflectionClass($denormalized);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {

            $subModel = $this->reader->getPropertyAnnotation($reflectionProperty, SubModel::class);
            if ($subModel instanceof SubModel) {
                $reflectionProperty->setAccessible(true);
                if ($reflectionProperty->getValue($denormalized) != null) {
                    $reflectionProperty->setValue(
                        $denormalized,
                        $this->modelBuilder(
                            $reflectionProperty->getValue($denormalized),
                            $subModel->model
                        )
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

    /**
     * @param Resource $annotation
     * @param Repo $repo
     * @return string
     * @throws RuntimeError
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\SyntaxError
     */
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

    /**
     * @param string $model
     * @param string $identifier
     * @return Resource
     * @throws \ReflectionException
     */
    private function getResource(string $model, string $identifier): Resource
    {
        $reflection = new \ReflectionClass($model);

        $resourceInterface = $this->reader->getClassAnnotation($reflection, ResourceInterface::class);
        if ($resourceInterface == null && $reflection->getParentClass()){
            return $this->getResource($reflection->getParentClass()->getName(), $identifier);
        }

        if ($resourceInterface instanceof Resources) {
            if (!array_key_exists($identifier, $resourceInterface->resources)) {
                throw new \Exception(sprintf('Identifier: "%s" does not exists in Model: "%s"', $identifier, $model));
            }
            return $resourceInterface->resources[$identifier];
        }
        return $resourceInterface;
    }
}
