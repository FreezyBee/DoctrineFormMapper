<?php

declare(strict_types=1);

namespace FreezyBee\DoctrineFormMapper\Tests\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
class UuidProduct
{
    #[ORM\Column(type: 'uuid'), ORM\Id]
    private UuidInterface $id;

    #[ORM\Column]
    private string $name;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->name = '';
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
