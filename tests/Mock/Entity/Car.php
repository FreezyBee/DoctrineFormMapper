<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Tests\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Car
{
    #[ORM\Column, ORM\Id, ORM\GeneratedValue]
    private int $id = 0;

    #[ORM\Column]
    private string $license = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getLicense(): string
    {
        return $this->license;
    }

    public function setLicense(string $license): void
    {
        $this->license = $license;
    }
}
