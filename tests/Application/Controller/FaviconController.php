<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Application\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Browsers request /favicon.ico on every visit; without a route the sandbox logs a noisy 404.
 */
final class FaviconController
{
    public function __invoke(): Response
    {
        return new Response('', 204, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
