<?php

declare(strict_types=1);

namespace MonterHealth\ApiFilterBundle\Tests\Application\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class Address
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $city;

    public function __construct(string $city)
    {
        $this->city = $city;
    }

    public function getCity(): string
    {
        return $this->city;
    }
}

