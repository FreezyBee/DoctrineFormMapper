<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Mappers;

use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\SmartObject;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class Embedded implements IComponentMapper
{
    use SmartObject;

    private DoctrineFormMapper $mapper;

    private PropertyAccessorInterface $accessor;

    public function __construct(DoctrineFormMapper $mapper)
    {
        $this->mapper = $mapper;
        $this->accessor = $mapper->getAccessor();
    }

    public function load(ClassMetadata $meta, IComponent $component, $entity): bool
    {
        if (!$component instanceof Container) {
            return false;
        }

        $name = $component->getName() ?: '';

        if (isset($meta->embeddedClasses[$name])) {
            $this->mapper->load($this->accessor->getValue($entity, $name), $component);
            return true;
        }

        return false;
    }

    public function save(ClassMetadata $meta, IComponent $component, &$entity): bool
    {
        if (!$component instanceof Container) {
            return false;
        }

        $name = $component->getName() ?: '';

        if (isset($meta->embeddedClasses[$name])) {
            $embeddedEntity = $this->accessor->getValue($entity, $name);
            $this->mapper->save($embeddedEntity, $component);
            return true;
        }

        return false;
    }
}
