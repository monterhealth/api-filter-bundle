<?php

namespace MonterHealth\ApiFilterBundle\Attribute;

class Reader
{
    public function getFilterAttributes(\ReflectionClass | \ReflectionProperty $reflector): \Iterator
    {
        $attributes = $reflector->getAttributes(ApiFilter::class);

        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}