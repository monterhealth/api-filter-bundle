<?php

declare(strict_types=1);

use MonterHealth\ApiFilterBundle\Tests\Application\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require \dirname(__DIR__, 3).'/vendor/autoload.php';

if (class_exists(Dotenv::class)) {
    (new Dotenv())->bootEnv(\dirname(__DIR__, 3).'/.env');
}

$kernel = new Kernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
