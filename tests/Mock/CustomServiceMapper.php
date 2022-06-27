<?php

declare(strict_types=1);

namespace FreezyBee\DoctrineFormMapper\Tests\Mock;

use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use Nette\ComponentModel\IComponent;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class CustomServiceMapper implements IComponentMapper
{
    public function __construct(DoctrineFormMapper $mapper, CustomService $customService, string $param)
    {
        // fake
        $x = [
            $mapper,
            $customService,
            $param,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ClassMetadata $meta, IComponent $component, $entity): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ClassMetadata $meta, IComponent $component, &$entity): bool
    {
        return false;
    }
}
