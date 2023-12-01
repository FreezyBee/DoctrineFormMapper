<?php

declare(strict_types=1);

namespace FreezyBee\DoctrineFormMapper\Tests\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Pond
{
    #[ORM\Column, ORM\Id, ORM\GeneratedValue]
    private int $id = 0;

    #[ORM\Embedded]
    private EmbeddableFrog $frog;

    public function __construct()
    {
        $this->frog = new EmbeddableFrog();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getFrog(): EmbeddableFrog
    {
        return $this->frog;
    }

    public function setFrog(EmbeddableFrog $frog): void
    {
        $this->frog = $frog;
    }
}
