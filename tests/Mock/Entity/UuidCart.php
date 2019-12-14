<?php

declare(strict_types=1);

namespace FreezyBee\DoctrineFormMapper\Tests\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 */
class UuidCart
{
    /**
     * @var UuidInterface
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var UuidProduct
     * @ORM\ManyToOne(targetEntity="FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\UuidProduct")
     */
    private $product;

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return UuidProduct
     */
    public function getProduct(): UuidProduct
    {
        return $this->product;
    }

    /**
     * @param UuidProduct $product
     */
    public function setProduct(UuidProduct $product): void
    {
        $this->product = $product;
    }
}
