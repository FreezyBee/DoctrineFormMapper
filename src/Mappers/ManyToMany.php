<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Mappers;

use Doctrine\Common\Collections\Collection;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\Utils\RelationsHelper;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\MultiChoiceControl;
use Nette\SmartObject;
use TypeError;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
class ManyToMany implements IComponentMapper
{
    use SmartObject;
    use RelationsHelper;

    /**
     * {@inheritdoc}
     */
    public function load(ClassMetadata $meta, Component $component, $entity): bool
    {
        if (!$component instanceof MultiChoiceControl) {
            return false;
        }

        $name = $component->getName() ?: '';

        if (!$meta->hasAssociation($name)) {
            return false;
        }

        // set default items
        $this->setDefaultItems($component, $entity);

        /** @var Collection<int|string, mixed>|null $collection */
        $collection = null;

        try {
            $collection = $this->accessor->getValue($entity, $name);
        } catch (TypeError $error) {
            $pattern = '/must be (of the type|an instance of) .*, null returned$/';
            if (!preg_match($pattern, $error->getMessage())) {
                throw $error;
            }
        }

        if ($collection) {
            $UoW = $this->em->getUnitOfWork();

            $values = [];
            foreach ($collection as $relation) {
                $values[] = $UoW->getSingleIdentifierValue($relation);
            }

            $component->setDefaultValue($values);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ClassMetadata $meta, Component $component, &$entity): bool
    {
        if (!$component instanceof MultiChoiceControl) {
            return false;
        }

        $name = $component->getName() ?: '';

        if (!$meta->hasAssociation($name)) {
            return false;
        }

        $valueIdentifiers = $component->getValue();

        /** @var Collection<int|string, mixed> $collection */
        $collection = $this->accessor->getValue($entity, $name);
        $collection->clear();

        if ($valueIdentifiers) {
            $repository = $this->em->getRepository($this->relatedMetadata($entity, $name)->getName());

            foreach ($valueIdentifiers as $id) {
                $relationEntity = $repository->find($id);

                if ($relationEntity) {
                    $collection->add($relationEntity);
                }
            }
        }

        return true;
    }
}
