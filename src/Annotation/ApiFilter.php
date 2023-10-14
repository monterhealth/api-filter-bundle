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

namespace MonterHealth\ApiFilterBundle\Annotation;

use MonterHealth\ApiFilterBundle\Filter\Filter;

/**
 * Filter attribute.
 *
 * @author Antoine Bluchet <soyuka@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class ApiFilter
{

    public string $id;
    public string $strategy;
    public string $filterClass;
    public array $properties = [];
    public array $arguments = [];

    public function __construct(array $options = [])
    {
        if (!\is_string($options['value'] ?? null)) {
            throw new \InvalidArgumentException('This annotation needs a value representing the filter class.');
        }

        if (!is_a($options['value'], Filter::class, true)) {
            throw new \InvalidArgumentException(sprintf('The filter class "%s" does not implement "%s".', $options['value'], Filter::class));
        }

        $this->filterClass = $options['value'];
        unset($options['value']);

        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('Property "%s" does not exist on the ApiFilter annotation.', $key));
            }

            $this->$key = $value;
        }
    }
}
