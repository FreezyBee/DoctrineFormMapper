<?php

declare(strict_types=1);

namespace FreezyBee\DoctrineFormMapper\Tests\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
class UuidCart
{
    #[ORM\Column(type: 'uuid'), ORM\Id]
    private UuidInterface $id;

    #[ORM\ManyToOne]
    private ?UuidProduct $product = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getProduct(): ?UuidProduct
    {
        return $this->product;
    }

    public function setProduct(UuidProduct $product): void
    {
        $this->product = $product;
    }
}
