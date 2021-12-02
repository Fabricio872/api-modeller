
![GitHub release](https://img.shields.io/github/v/release/Fabricio872/api-modeller)
![GitHub last commit](https://img.shields.io/github/last-commit/Fabricio872/api-modeller)
[![PHP Composer Test and Tag](https://github.com/Fabricio872/api-modeller/actions/workflows/php-8.0-test.yml/badge.svg)](https://github.com/Fabricio872/api-modeller/actions/workflows/php-8.0-test.yml)
![Packagist Downloads](https://img.shields.io/packagist/dt/Fabricio872/api-modeller)
![GitHub Repo stars](https://img.shields.io/github/stars/Fabricio872/api-modeller?style=social)

Valuable partners:

![PhpStorm logo](https://resources.jetbrains.com/storage/products/company/brand/logos/PhpStorm.svg)

Before installation
===================

> If you are using older php then version 7.2 download with command

```console
$ composer require fabricio872/api-modeller:^1.0
```

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 1: Download the Library

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require fabricio872/api-modeller
```

### Step 2: Initialize library

Create new instance of `Fabricio872\ApiModeller\Modeller`. For legacy project is easiest to implement it with Singleton pattern which gives you 
instance anywhere where you call it as described [here](#calling-the-api).

> create new class somewhere in your composer autoload directory with name Modeller and add your namespace
```php
use Doctrine\Common\Annotations\AnnotationReader;
use Fabricio872\ApiModeller\ClientAdapter\Symfony;
use Symfony\Component\HttpClient\HttpClient;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Modeller
{
    /** @var \Fabricio872\ApiModeller\Modeller */
    private static $modeller;

    public static function get()
    {
        if (!isset(self::$modeller)) {
            $reader = new AnnotationReader();

            $httpClient = HttpClient::create();
            $client = new Symfony($httpClient);

            $loader = new FilesystemLoader();
            $twig = new Environment($loader, [
                'cache' => '/path/to/compilation_cache',
            ]);

            self::$modeller = new \Fabricio872\ApiModeller\Modeller(
                $reader,
                $client,
                $twig
            );
        }
        return self::$modeller;
    }
}

```

# Usage
> This lib uses models with Annotations similar to Doctrine Entities.
>
> Usually they are in a directory `src/ApiModels` but they are not required to be there as long as they have correct namespace

## Example model with single Resource

This is example of a model for receiving list of users from some API

```php
// src/ApiModels/Users.php

use Fabricio872\ApiModeller\Annotations\Resource;

/**
 * @Resource(
 *     endpoint="{{api_url}}/api/users",
 *     method="GET",
 *     type="json",
 *     options={
 *          "headers"={
 *              "accept"= "application/json"
 *          }
 *     }
 * )
 */
class Users
{
    public $page;
    public $per_page;
    public $total;
    public $total_pages;
    public $data;
}
```

> endpoint parameter is endpoint which will be called.

> method parameter is method with which the request will be done 
> 
> default: "GET"

> type parameter defines format of the received data 
> 
> currently supported: "json", "xml'
> 
> default: "json"

> options parameter is array that is directly passed (but can be altered as explained in [setOptions](#setoptions) section) 
> to [symfony/http-client](https://github.com/symfony/http-client) request method as 3. parameter so use [this documentation](https://symfony.com/doc/current/http_client.html) 

## Example model with multiple Resources
To define multiple resources you need to wrap multiple Resource annotation into single Resources annotation with identifier at beginning.
This identifier is then used while calling this endpoint as described in section [setIdentifier](#setidentifier)

```php
// src/ApiModels/Users.php

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
class Users
{
    public $page;
    public $per_page;
    public $total;
    public $total_pages;
    public $data;
}
```

## Calling the API

> Instance of class `Fabricio872\ApiModeller\Modeller` can be received like this if configuration was as described [here](#step-2-initialize-library)

This controller dumps model or collection of models form [this example](#example-model-with-single-resource) with namespace `Users::class`
and sets query parameter 'page' to 2
```php
// src/Controller/SomeController.php

    public function index()
    {
        var_dump(Modeller::get()->getData(
            Repo::new(Users::class)
                ->setOptions([
                    "query" => [
                        "page" => 2
                    ]
                ])
        ));
    }
```
> Notice `setOptions` have alternative function `addOptions` which merges existing and provided options

> Notice that `Modeller::get()` must have correct namespace pointing to class from [configuration section](#step-2-initialize-library)

This controller dumps model or collection of models form [this example](#example-model-with-multiple-resources) with namespace `Users::class`
and fills the {{id}} variable from model with number 2

noticed that now method setIdentifier is required
```php
// src/Controller/SomeController.php

    public function index(Modeller $modeller)
    {
        var_dump(Modeller::get()->getData(
            Repo::new(Users::class)
                ->setParameters([
                    "id" => 2
                ])
                ->setIdentifier("single")
        ));
    }
```

> The modeller accepts Repo object which requires namespace of model you want to build
> and has optional setters:
> - setOptions()
> - setParameters()
> - setIdentifier()

### setOptions
This method accepts array of options that will be merged with options configured in a model (and will override overlapped parameters) 
to [symfony/http-client](https://github.com/symfony/http-client) request method as 3. parameter so use [this documentation](https://symfony.com/doc/current/http_client.html) 

### setParameters
This method accepts array and sets twig variables (same as if you render a template but here the template is endpoint parameter
from model) to url configuration and can override global twig variables

### setIdentifier
This method is required in case when you use multiple Resources for single model as shown in [this example](#example-model-with-multiple-resources)
