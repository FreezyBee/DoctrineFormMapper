<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Mappers;

use FreezyBee\DoctrineFormMapper\IComponentMapper;
use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\Utils\RelationsHelper;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\ChoiceControl;
use Nette\SmartObject;
use TypeError;

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
    public function load(ClassMetadata $meta, Component $component, $entity): bool
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
        } catch (TypeError $error) {
            if (!preg_match('/must be an instance of [a-zA-Z\\\]+, null returned$/', $error->getMessage())) {
                throw $error;
            }
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
    public function save(ClassMetadata $meta, Component $component, &$entity): bool
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
            $repository = $this->em->getRepository($this->relatedMetadata($entity, $name)->getName());
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
