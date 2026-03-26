<?php

declare(strict_types=1);

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use MonterHealth\ApiFilterBundle\Tests\Application\Controller\AuthorController;
use MonterHealth\ApiFilterBundle\Tests\Application\Controller\BookController;
use MonterHealth\ApiFilterBundle\Tests\Application\Controller\FaviconController;

// Explicit route wiring for the sandbox.
// This avoids relying on attribute-route loading in the micro-kernel setup.
$routes = new RouteCollection();
$routes->add(
    'sandbox_books_index',
    new Route(
        '/books',
        [
            // Class + method (double colon); single colon is service-id syntax and breaks here.
            '_controller' => BookController::class . '::index',
        ],
        [],
        [],
        '',
        [],
        ['GET']
    )
);

$routes->add(
    'sandbox_authors_index',
    new Route(
        '/authors',
        [
            '_controller' => AuthorController::class . '::index',
        ],
        [],
        [],
        '',
        [],
        ['GET']
    )
);

$routes->add(
    'sandbox_favicon',
    new Route(
        '/favicon.ico',
        [
            '_controller' => FaviconController::class,
        ],
        [],
        [],
        '',
        [],
        ['GET', 'HEAD']
    )
);

return $routes;

