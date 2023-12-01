<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\Exceptions\InvalidStateException;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use LogicException;
use Nette\Forms\Controls\ChoiceControl;
use Nette\Forms\Controls\MultiChoiceControl;
use Stringable;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
trait RelationsHelper
{
    protected EntityManagerInterface $em;

    protected PropertyAccessorInterface $accessor;

    public function __construct(DoctrineFormMapper $mapper)
    {
        $this->em = $mapper->getEntityManager();
        $this->accessor = $mapper->getAccessor();
    }

    /**
     * @param MultiChoiceControl|ChoiceControl $component
     * @param mixed $entity
     */
    public function setDefaultItems($component, $entity): void
    {
        if (count($component->getItems()) === 0) {
            $associationKeyOrCallback = $component->getOption(IComponentMapper::ITEMS_TITLE);

            if ($associationKeyOrCallback === null) {
                throw new InvalidStateException('Use IComponentMapper::ITEMS_TITLE to specify items title or callback');
            }

            $criteria = $component->getOption(IComponentMapper::ITEMS_FILTER) ?? [];
            $orderBy = $component->getOption(IComponentMapper::ITEMS_ORDER) ?? [];

            $name = $component->getName();
            if (!$name) {
                throw new InvalidStateException('Component name is null or blank');
            }

            $related = $this->relatedMetadata($entity, $name);
            $items = $this->findPairs($related, $associationKeyOrCallback, $criteria, $orderBy);
            $component->setItems($items);
        }
    }

    /**
     * @param mixed $entity
     * @return ClassMetadata<object>
     */
    protected function relatedMetadata($entity, string $relationName): ClassMetadata
    {
        $className = get_class($entity);
        assert(is_string($className));

        $meta = $this->em->getClassMetadata($className);
        /** @var class-string $targetClass */
        $targetClass = $meta->getAssociationTargetClass($relationName);
        return $this->em->getClassMetadata($targetClass);
    }

    /**
     * @param ClassMetadata<object> $meta
     * @param callable|string $associationKeyOrCallback
     * @param array<string, mixed>|callable $criteria
     * @param string[] $orderBy
     * @return mixed[]
     */
    protected function findPairs(ClassMetadata $meta, $associationKeyOrCallback, $criteria, array $orderBy): array
    {
        $classname = $meta->getName();
        if (!class_exists($classname)) {
            throw new LogicException();
        }

        $items = [];
        $idKey = $meta->getSingleIdentifierFieldName();

        if (is_callable($criteria)) {
            $qb = $this->em->createQueryBuilder()
                ->select('entity')
                ->from($classname, 'entity');

            // call user func
            $criteria($qb);

            $entities = $qb->getQuery()->getResult();
        } else {
            $entities = $this->em->getRepository($classname)->findBy($criteria, $orderBy);
        }

        foreach ($entities as $entity) {
            $identifier = $this->accessor->getValue($entity, $idKey);
            if (is_object($identifier) && $identifier instanceof Stringable) {
                // support for UuidInterface
                $identifier = (string) $identifier;
            }

            $items[$identifier] = is_callable($associationKeyOrCallback) ?
                $associationKeyOrCallback($entity) :
                $this->accessor->getValue($entity, $associationKeyOrCallback);
        }

        return $items;
    }
}
