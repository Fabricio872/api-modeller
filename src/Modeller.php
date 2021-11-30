<?php

declare(strict_types=1);

namespace Fabricio872\ApiModeller;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use Fabricio872\ApiModeller\Annotations\Resource;
use Fabricio872\ApiModeller\Annotations\ResourceInterface;
use Fabricio872\ApiModeller\Annotations\Resources;
use Fabricio872\ApiModeller\ClientAdapter\ClientInterface;
use GuzzleHttp\Exception\ClientException;
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
     * @return ArrayCollection|mixed
     */
    public function getData()
    {
        $normalizedContent = self::getSerializer()->decode((string)$this->getRawData(), $this->getAnnotation()->type);
        $return = new ArrayCollection();
        if (array_values($normalizedContent) === $normalizedContent) {
            foreach ($normalizedContent as $normalizedItem) {
                $return->add(self::getSerializer()->denormalize($normalizedItem, $this->repo->getModel()));
            }
            return $return;
        }
        return self::getSerializer()->denormalize($normalizedContent, $this->repo->getModel());
    }

    public function getRawData()
    {
        try {
            return $this->client->request(
                $this->getMethod(),
                $this->getEndpoint(),
                $this->getOptions()
            );
        } catch (ClientException $exception){
            throw new \Exception($exception->getResponse()->getBody()->getContents(), $exception->getCode());
        }
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

    public function getMethod()
    {
        return $this->getAnnotation()->method;
    }

    public function getEndpoint()
    {
        return self::renderEndpoint($this->getAnnotation(), $this->repo);
    }

    public function getOptions()
    {
        return array_merge_recursive($this->getAnnotation()->options, $this->repo->getOptions());
    }

    private function getAnnotation()
    {
        return $this->getResource($this->repo->getModel(), $this->repo->getIdentifier());
    }

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
