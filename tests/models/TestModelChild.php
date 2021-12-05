<?php

namespace Tests\models;

use Fabricio872\ApiModeller\Annotations\SubModel;

class TestModelChild extends TestModelAbstract
{
    public $method;
    public $endpoint;
    public $options;
    /**
     * @SubModel(TestSubModel::class)
     */
    public $subClass;
}