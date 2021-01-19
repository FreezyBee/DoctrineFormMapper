<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Mappers;

use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use LogicException;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use TypeError;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
class Column implements IComponentMapper
{
    use SmartObject;

    /** @var PropertyAccessor */
    private $accessor;

    /**
     * @param DoctrineFormMapper $mapper
     */
    public function __construct(DoctrineFormMapper $mapper)
    {
        $this->accessor = $mapper->getAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ClassMetadata $meta, IComponent $component, $entity): bool
    {
        if (!$component instanceof BaseControl) {
            return false;
        }

        $name = $component->getName() ?: '';

        if ($meta->hasField($name)) {
            try {
                $value = $this->accessor->getValue($entity, $name);
                $component->setDefaultValue($value);
            } catch (TypeError $error) {
                if (!preg_match('/ null returned$/', $error->getMessage())) {
                    throw $error;
                }
            } catch (AccessException $e) {
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ClassMetadata $meta, IComponent $component, &$entity): bool
    {
        if (!$component instanceof BaseControl) {
            return false;
        }

        $name = $component->getName() ?: '';

        if ($meta->hasField($name) && $this->accessor->isWritable($entity, $name)) {
            try {
                $this->accessor->setValue($entity, $name, $component->getValue());
            } catch (LogicException $e) {
                return false;
            }

            return true;
        }

        return false;
    }
}
