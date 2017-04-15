<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Mappers;

use Doctrine\Common\Collections\Collection;
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use Doctrine\ORM\Mapping\ClassMetadata;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\SmartObject;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
class OneToOne implements IComponentMapper
{
    use SmartObject;

    /** @var DoctrineFormMapper */
    private $mapper;

    /**
     * @param DoctrineFormMapper $mapper
     */
    public function __construct(DoctrineFormMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ClassMetadata $meta, Component $component, $entity): bool
    {
        if (!$component instanceof Container) {
            return false;
        }

        $relationEntity = $this->getRelation($meta, $entity, $component->getName());

        if ($relationEntity === null) {
            return false;
        }

        $this->mapper->load($relationEntity, $component);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ClassMetadata $meta, Component $component, &$entity): bool
    {
        if (!$component instanceof Container) {
            return false;
        }

        $relationEntity = $this->getRelation($meta, $entity, $component->getName());

        if ($relationEntity === null) {
            return false;
        }

        $this->mapper->save($relationEntity, $component);

        return true;
    }

    /**
     * @param ClassMetadata $meta
     * @param mixed $entity
     * @param string $field
     * @return mixed|null
     */
    private function getRelation(ClassMetadata $meta, $entity, string $field)
    {
        if (!$meta->hasAssociation($field) || !$meta->isSingleValuedAssociation($field)) {
            return null;
        }

        $relationEntity = $meta->getFieldValue($entity, $field);

        if ($relationEntity === null) {
            $class = $meta->getAssociationTargetClass($field);
            $relationMeta = $this->mapper->getEntityManager()->getClassMetadata($class);

            $relationEntity = $relationMeta->newInstance();
            $meta->setFieldValue($entity, $field, $relationEntity);
        }

        return $relationEntity;
    }
}
