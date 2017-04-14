<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Mappers;

use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\Exceptions\InvalidStateException;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class Construct implements IComponentMapper
{
    use SmartObject;

    /** @var EntityManager */
    private $entityManager;

    /**
     * @param DoctrineFormMapper $mapper
     */
    public function __construct(DoctrineFormMapper $mapper)
    {
        $this->entityManager = $mapper->getEntityManager();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ClassMetadata $meta, Component $component, $entity): bool
    {
        return false;
    }

    /**
     * Try create new instance by class name - entity
     * {@inheritdoc}
     */
    public function save(ClassMetadata $meta, Component $component, &$entity): bool
    {
        if (is_object($entity)) {
            return false;
        }

        if (!$component instanceof Container) {
            return false;
        }

        $reflection = $meta->getReflectionClass();
        $constructor = $reflection->getConstructor();

        if ($constructor) {
            $constructorNewParameters = [];

            foreach ($constructor->getParameters() as $constructorParameter) {
                $name = $constructorParameter->getName();

                /** @var BaseControl $child */
                $child = $component->getComponent($name, false);

                if ($child === null && $constructorParameter->isOptional() === false) {
                    throw new InvalidStateException("Can't create new instance: control '$name' is missing");
                }

                if ($constructorParameter->getClass() !== null) {
                    // object type
                    $class = $meta->getAssociationTargetClass($name);
                    $constructorNewParameters[$name] = $this->entityManager->find($class, $child->getValue());
                } else {
                    // scalar type
                    $constructorNewParameters[$name] = $child->getValue();
                }
            }

            $entity = $reflection->newInstanceArgs($constructorNewParameters);
        } else {
            $entity = $reflection->newInstanceWithoutConstructor();
        }

        return false;
    }
}
