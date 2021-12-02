<?php

namespace Tests\src;

use Fabricio872\ApiModeller\Repo;
use PHPUnit\Framework\TestCase;
use Tests\TestModel;

class RepoTest extends TestCase
{

    public function testOptions()
    {
        $repo = new Repo(
            TestModel::class
        );
        $options1 = [
            "param1" => true,
            "param2" => 2,
            "param3" => "test",
            "param4" => ["test" => 69]
        ];

        $repo->setOptions($options1);

        self::assertEquals($options1, $repo->getOptions());

        $options2 = [
            "param1" => false,
            "param4" => ["test2" => 420]
        ];

        $repo->addOptions($options2);

        self::assertEquals([
            "param1" => false,
            "param2" => 2,
            "param3" => "test",
            "param4" => ["test" => 69, "test2" => 420]
        ], $repo->getOptions());
    }

    public function testNew()
    {
        $model = Repo::new(TestModel::class);

        $this->assertInstanceOf(Repo::class, $model);
        $this->assertEquals(TestModel::class, $model->getModel());
    }
}
