<?php

declare(strict_types=1);

namespace FreezyBee\DoctrineFormMapper\Tests\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Pond
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @var EmbeddableFrog
     * @ORM\Embedded(class="FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\EmbeddableFrog")
     */
    private $frog;

    /**
     *
     */
    public function __construct()
    {
        $this->frog = new EmbeddableFrog();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return EmbeddableFrog
     */
    public function getFrog(): EmbeddableFrog
    {
        return $this->frog;
    }

    /**
     * @param EmbeddableFrog $frog
     */
    public function setFrog(EmbeddableFrog $frog)
    {
        $this->frog = $frog;
    }
}
