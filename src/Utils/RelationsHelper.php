<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Utils;

use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use FreezyBee\DoctrineFormMapper\Exceptions\InvalidStateException;
use Kdyby\Doctrine\EntityManager;
use Nette\Forms\Controls\ChoiceControl;
use Nette\Forms\Controls\MultiChoiceControl;
use Nette\Utils\Callback;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
trait RelationsHelper
{
    /** @var EntityManager */
    private $em;

    /** @var PropertyAccessor */
    private $accessor;

    /**
     * @param DoctrineFormMapper $mapper
     */
    public function __construct(DoctrineFormMapper $mapper)
    {
        $this->em = $mapper->getEntityManager();
        $this->accessor = $mapper->getAccessor();
    }

    /**
     * @param MultiChoiceControl|ChoiceControl $component
     * @param mixed $entity
     */
    public function setDefaultItems($component, $entity)
    {
        // set items
        if (count($component->getItems()) === 0) {
            $associationKeyOrCallback = $component->getOption(IComponentMapper::ITEMS_TITLE, false);

            if (!$associationKeyOrCallback) {
                throw new InvalidStateException('Use IComponentMapper::ITEMS_TITLE to specify items title or callback');
            }

            $criteria = $component->getOption(IComponentMapper::ITEMS_FILTER, []);
            $orderBy = $component->getOption(IComponentMapper::ITEMS_ORDER, []);

            $related = $this->relatedMetadata($entity, $component->getName());
            $items = $this->findPairs($related, $associationKeyOrCallback, $criteria, $orderBy);
            $component->setItems($items);
        }
    }

    /**
     * @param mixed $entity
     * @param string $relationName
     * @return ClassMetadata
     */
    private function relatedMetadata($entity, string $relationName): ClassMetadata
    {
        $meta = $this->em->getClassMetadata(get_class($entity));
        $targetClass = $meta->getAssociationTargetClass($relationName);
        return $this->em->getClassMetadata($targetClass);
    }

    /**
     * @param ClassMetadata $meta
     * @param callable|string $associationKeyOrCallback
     * @param array $criteria
     * @param array $orderBy
     * @return array
     */
    private function findPairs(ClassMetadata $meta, $associationKeyOrCallback, array $criteria, array $orderBy): array
    {
        $repository = $this->em->getRepository($meta->getName());

        $items = [];
        $idKey = $meta->getSingleIdentifierFieldName();
        foreach ($repository->findBy($criteria, $orderBy) as $entity) {
            $items[$this->accessor->getValue($entity, $idKey)] = is_callable($associationKeyOrCallback) ?
                Callback::invoke($associationKeyOrCallback, $entity) :
                $this->accessor->getValue($entity, $associationKeyOrCallback);
        }

        return $items;
    }
}
