<?php

namespace Tests;

use Fabricio872\ApiModeller\Annotations\Resource;
use Fabricio872\ApiModeller\Annotations\Resources;

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
 * })
 */
abstract class TestModelAbstract
{
}