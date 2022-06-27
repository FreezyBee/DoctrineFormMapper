<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Mappers;

use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use FreezyBee\DoctrineFormMapper\Utils\RelationsHelper;
use LogicException;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\ChoiceControl;
use Nette\SmartObject;
use Symfony\Component\PropertyAccess\Exception\AccessException;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
class ManyToOne implements IComponentMapper
{
    use SmartObject;
    use RelationsHelper;

    /**
     * {@inheritdoc}
     */
    public function load(ClassMetadata $meta, IComponent $component, $entity): bool
    {
        if (!$component instanceof ChoiceControl) {
            return false;
        }

        $name = $component->getName() ?: '';

        if (!$meta->hasAssociation($name)) {
            return false;
        }

        // set items
        $this->setDefaultItems($component, $entity);

        // set default value
        try {
            $relation = $this->accessor->getValue($entity, $name);
        } catch (AccessException $e) {
            $relation = null;
        }

        if ($relation) {
            $UoW = $this->em->getUnitOfWork();
            $component->setDefaultValue($UoW->getSingleIdentifierValue($relation));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ClassMetadata $meta, IComponent $component, &$entity): bool
    {
        if (!$component instanceof ChoiceControl) {
            return false;
        }

        $name = $component->getName() ?: '';

        if (!$meta->hasAssociation($name)) {
            return false;
        }

        $valueIdentifier = $component->getValue();

        if ($valueIdentifier) {
            $classname = $this->relatedMetadata($entity, $name)->getName();
            if (!class_exists($classname)) {
                throw new LogicException();
            }

            $repository = $this->em->getRepository($classname);
            $relationEntity = $repository->find($valueIdentifier);

            if ($relationEntity) {
                $meta->setFieldValue($entity, $name, $relationEntity);
            }
        } else {
            $meta->setFieldValue($entity, $name, null);
        }

        return true;
    }
}
