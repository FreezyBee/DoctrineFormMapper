<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Nette\Forms\Container;
use Nette\SmartObject;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
class DoctrineFormMapper
{
    use SmartObject;

    /** @var EntityManager */
    protected $em;

    /** @var IComponentMapper[] */
    protected $componentMappers = [];

    /** @var PropertyAccessor */
    protected $accessor;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param IComponentMapper $componentMapper
     */
    public function addMapper(IComponentMapper $componentMapper)
    {
        $this->componentMappers[] = $componentMapper;
    }

    /**
     * @return PropertyAccessor
     */
    public function getAccessor(): PropertyAccessor
    {
        if ($this->accessor === null) {
            $this->accessor = new PropertyAccessor(true);
        }

        return $this->accessor;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->em;
    }

    /**
     * @param mixed $entity
     * @param Container $formElement
     */
    public function load($entity, Container $formElement)
    {
        $meta = $this->getMetadata($entity);

        if (is_string($entity)) {
            // init object from class name
            $entity = (new \ReflectionClass($entity))->newInstanceWithoutConstructor();
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
     * @param mixed $entity
     * @param Container $formElement
     */
    public function save(&$entity, Container $formElement)
    {
        $meta = $this->getMetadata($entity);

        foreach ($formElement->getComponents() as $component) {
            foreach ($this->componentMappers as $mapper) {
                if ($mapper->save($meta, $component, $entity)) {
                    break;
                }
            }
        }
    }

    /**
     * @param mixed $entity
     * @return ClassMetadata
     */
    private function getMetadata($entity): ClassMetadata
    {
        return $this->em->getClassMetadata(is_object($entity) ? get_class($entity) : $entity);
    }
}
