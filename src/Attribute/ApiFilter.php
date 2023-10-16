<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file at https://github.com/api-platform/core/blob/master/LICENSE
 */

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Attribute;

use MonterHealth\ApiFilterBundle\Filter\Filter;

/**
 * Filter attribute.
 *
 * @author Antoine Bluchet <soyuka@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class ApiFilter
{

    public string | null $id = null;
    public string | null $strategy = null;
    public string $filterClass;
    public array $properties = [];
    public array $arguments = [];

    public function __construct(string $filterClass, array $properties = [], array $arguments = [])
    {
        if (!is_a($filterClass, Filter::class, true)) {
            throw new \InvalidArgumentException(sprintf('The filter class "%s" does not implement "%s".', $filterClass, Filter::class));
        }

        $this->filterClass = $filterClass;
        $this->properties = $properties;
        $this->arguments = $arguments;
    }
}
