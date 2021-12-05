<?php

namespace Tests\src;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Fabricio872\ApiModeller\Modeller;
use Fabricio872\ApiModeller\Repo;
use PHPUnit\Framework\TestCase;
use Tests\models\TestModel;
use Tests\models\TestTitledModel;
use Tests\models\TestSubModel;
use Tests\TestClient;
use Tests\TestClientEmpty;
use Tests\TestTitledClient;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ModellerTest extends TestCase
{

    public function testGetEndpoint()
    {
        $modeller = $this->getModeller();
        $modeller->setRepo($this->getRepo()->setIdentifier('multiple'));

        self::assertEquals("http://test.com/api/users", $modeller->getEndpoint());
    }

    public function testGetRawData()
    {
        $modeller = $this->getModeller();
        $modeller->setRepo($this->getRepo()->setIdentifier('multiple'));

        self::assertEquals(json_encode([
            "method" => "GET",
            "endpoint" => "http://test.com/api/users",
            "options" => [
                "headers" => [
                    "accept" => "application/json"
                ]
            ],
            "subClass" => [
                "sub1" => true,
                "sub2" => 420,
                "sub3" => "test"
            ]
        ]), $modeller->getRawData());
    }

    public function testGetOptions()
    {
        $modeller = $this->getModeller();
        $modeller->setRepo($this->getRepo()->setIdentifier('multiple')->setOptions([
            "test1" => "test"
        ]));

        self::assertEquals([
            "test1" => "test",
            "headers" => [
                "accept" => "application/json"
            ]
        ], $modeller->getOptions());
    }

    public function testGetMethod()
    {
        $modeller = $this->getModeller();
        $modeller->setRepo($this->getRepo()->setIdentifier('multiple'));

        self::assertEquals("GET", $modeller->getMethod());
    }

    public function testGetData()
    {
        $modeller = $this->getModeller();
        $modeller->setRepo($this->getRepo()->setIdentifier('multiple'));

        $subModel = new TestSubModel();
        $subModel->sub1 = true;
        $subModel->sub2 = 420;
        $subModel->sub3 = "test";

        $model = new TestModel();
        $model->endpoint = "http://test.com/api/users";
        $model->method = "GET";
        $model->subClass = $subModel;
        $model->options = [
            "headers" => [
                "accept" => "application/json"
            ]
        ];

        self::assertEquals($model, $modeller->getData());
    }

    public function testGetTitledData()
    {
        $modeller = $this->getTitledModeller();
        $modeller->setRepo($this->getTitledRepo()->setIdentifier('multiple'));

        $subModel = new TestSubModel();
        $subModel->sub1 = true;
        $subModel->sub2 = 420;
        $subModel->sub3 = "test";

        $model = new TestTitledModel();
        $model->endpoint = "http://test.com/api/users";
        $model->method = "GET";
        $model->subClass = $subModel;
        $model->options = [
            "headers" => [
                "accept" => "application/json"
            ]
        ];

        self::assertEquals($model, $modeller->getData());
    }

    public function testGetEmptyData()
    {
        $modeller = $this->getModellerEmpty();
        $modeller->setRepo($this->getRepo()->setIdentifier('multiple'));

        $model = new TestModel();
        $model->endpoint = "http://test.com/api/users";
        $model->method = "GET";
        $model->subClass = new ArrayCollection();
        $model->options = [
            "headers" => [
                "accept" => "application/json"
            ]
        ];

        self::assertEquals($model, $modeller->getData());
    }

    private function getModeller()
    {

        $reader = new AnnotationReader();

        $client = new TestClient();

        $loader = new FilesystemLoader();
        $twig = new Environment($loader);
        $twig->addGlobal("api_url", "http://test.com");

        return new Modeller(
            $reader,
            $client,
            $twig
        );
    }

    private function getModellerEmpty()
    {

        $reader = new AnnotationReader();

        $client = new TestClientEmpty();

        $loader = new FilesystemLoader();
        $twig = new Environment($loader);
        $twig->addGlobal("api_url", "http://test.com");

        return new Modeller(
            $reader,
            $client,
            $twig
        );
    }

    private function getTitledModeller()
    {

        $reader = new AnnotationReader();

        $client = new TestTitledClient();

        $loader = new FilesystemLoader();
        $twig = new Environment($loader);
        $twig->addGlobal("api_url", "http://test.com");

        return new Modeller(
            $reader,
            $client,
            $twig
        );
    }

    private function getRepo()
    {
        return Repo::new(TestModel::class)->setParameters([
            "id" => 420
        ]);
    }

    private function getTitledRepo()
    {
        return Repo::new(TestTitledModel::class)->setParameters([
            "id" => 420
        ]);
    }
}
