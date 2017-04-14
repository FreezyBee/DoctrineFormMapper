<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Tests\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * @ORM\Entity
 */
class Author
{
    use MagicAccessors;

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
    private $name;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $age;

    /**
     * @var Address
     * @ORM\OneToOne(targetEntity="Address")
     */
    private $address;

    /**
     * @var Car|null
     * @ORM\OneToOne(targetEntity="Car")
     */
    private $car;

    /**
     * @param string $name
     * @param Address $address
     */
    public function __construct(string $name, Address $address)
    {
        $this->name = $name;
        $this->address = $address;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param int $age
     */
    public function setAge(int $age)
    {
        $this->age = $age;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @return Car|null
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * @param Car|null $car
     */
    public function setCar($car)
    {
        $this->car = $car;
    }
}
