<?php
declare(strict_types=1);

namespace FreezyBee\DoctrineFormMapper\Tests\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;
use LogicException;

/**
 * @ORM\Entity
 */
class ImmutableThing
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $text;

    /**
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Fake method - simulating Kdyby\Doctrine\Entities\MagicAccessors or Nette\SmartObject
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        throw new LogicException;
    }
}
