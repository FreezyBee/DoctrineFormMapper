<?php

declare(strict_types=1);

namespace FreezyBee\DoctrineFormMapper\Tests\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;
use LogicException;

#[ORM\Entity]
class ImmutableThing
{
    #[ORM\Column, ORM\Id, ORM\GeneratedValue]
    private int $id = 0;

    #[ORM\Column]
    private string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Fake method - simulating Kdyby\Doctrine\Entities\MagicAccessors or Nette\SmartObject
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value): void
    {
        throw new LogicException();
    }
}
