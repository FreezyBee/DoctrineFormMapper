<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Nette\Forms\Container;
use Nette\SmartObject;
use ReflectionClass;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
class DoctrineFormMapper
{
    use SmartObject;

    protected EntityManagerInterface $em;

    /**
     * @var IComponentMapper[]
     */
    protected array $componentMappers = [];

    protected PropertyAccessorInterface $accessor;

    public function __construct(EntityManagerInterface $entityManager, ?PropertyAccessorInterface $accessor = null)
    {
        $this->em = $entityManager;
        $this->accessor = $accessor ?? new PropertyAccessor();
    }

    public function addMapper(IComponentMapper $componentMapper): void
    {
        $this->componentMappers[] = $componentMapper;
    }

    public function getAccessor(): PropertyAccessorInterface
    {
        return $this->accessor;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @param mixed|string $entity
     */
    public function load($entity, Container $formElement): void
    {
        $meta = $this->getMetadata($entity);

        if (is_string($entity) && class_exists($entity)) {
            // init object from class name
            $entity = (new ReflectionClass($entity))->newInstanceWithoutConstructor();
        }

        foreach ($formElement->getComponents() as $component) {
            foreach ($this->componentMappers as $mapper) {
                if ($mapper->load($meta, $component, $entity)) {
                    break;
                }
            }
        }
    }

    /**
     * @param mixed|string $entity
     * @return mixed
     */
    public function save($entity, Container $formElement)
    {
        $meta = $this->getMetadata($entity);

        foreach ($formElement->getComponents() as $component) {
            foreach ($this->componentMappers as $mapper) {
                if ($mapper->save($meta, $component, $entity)) {
                    break;
                }
            }
        }

        return $entity;
    }

    /**
     * @param mixed $entity
     * @return ClassMetadata<object>
     */
    private function getMetadata($entity): ClassMetadata
    {
        return $this->em->getClassMetadata(is_object($entity) ? get_class($entity) : $entity);
    }
}
