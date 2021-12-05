<?php

namespace Tests\models;

use Fabricio872\ApiModeller\Annotations\ModelTitle;
use Fabricio872\ApiModeller\Annotations\Resource;
use Fabricio872\ApiModeller\Annotations\Resources;
use Fabricio872\ApiModeller\Annotations\SubModel;

/**
 * @Resources({
 *      "multiple"= @Resource(
 *          endpoint="{{api_url}}/api/users",
 *          method="GET",
 *          type="json",
 *          options={
 *              "headers"={
 *                  "accept"= "application/json"
 *              }
 *          }
 *      ),
 *      "single"= @Resource(
 *          endpoint="{{api_url}}/api/users/{{id}}",
 *          method="GET",
 *          type="json",
 *          options={
 *              "headers"={
 *                  "accept"= "application/json"
 *              }
 *          }
 *      ),
 * }),
 * @ModelTitle("testTitle")
 */
class TestTitledModel
{
    public $method;
    public $endpoint;
    public $options;
    /**
     * @SubModel(TestSubModel::class)
     */
    public $subClass;
}