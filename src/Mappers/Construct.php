<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Mappers;

use Doctrine\ORM\EntityManagerInterface;
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\Exceptions\InvalidStateException;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use Doctrine\ORM\Mapping\ClassMetadata;
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

    /** @var EntityManagerInterface */
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

        $reflection = $meta->getReflectionClass();
        $constructor = $reflection->getConstructor();

        if ($constructor) {
            $constructorNewParameters = [];

            /** @var Container|null $baseComponent */
            $baseComponent = $component instanceof Container ? $component : $component->getParent();

            if ($baseComponent === null) {
                throw new InvalidStateException(__METHOD__ . ' cannot found container');
            }

            foreach ($constructor->getParameters() as $constructorParameter) {
                // property name
                $name = $constructorParameter->getName();

                /** @var BaseControl|Container|null $child */
                $child = $baseComponent->getComponent($name, false);

                // test if parameter is required and control exists
                if ($child === null && $constructorParameter->isOptional() === false) {
                    throw new InvalidStateException("Can't create new instance: control '$name' is missing");
                }

                if ($child === null) {
                    continue;
                }

                if ($meta->hasAssociation($name) === false) {
                    if ($child instanceof Container) {
                        throw new InvalidStateException('Scalar type and form container? What is wrong with you?');
                    }
                    // scalar type
                    $constructorNewParameters[$name] = $child->getValue();
                    continue;
                }

                // object type
                $targetClass = $meta->getAssociationTargetClass($name);

                if ($child instanceof Container) {
                    // probably OneToOne Container
                    $this->save($this->entityManager->getClassMetadata($targetClass), $child, $targetClass);
                    // $targetClass is new instance
                    $constructorNewParameters[$name] = $targetClass;
                } elseif ($child instanceof BaseControl) {
                    $constructorNewParameters[$name] = $this->entityManager->find($targetClass, $child->getValue());
                }
            }

            $entity = $reflection->newInstanceArgs($constructorNewParameters);
        } else {
            $entity = $reflection->newInstanceWithoutConstructor();
        }

        return false;
    }
}
