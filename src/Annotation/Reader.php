<?php

namespace MonterHealth\ApiFilterBundle\Annotation;

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