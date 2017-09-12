<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Mappers;

use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use Doctrine\ORM\Mapping\ClassMetadata;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
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
    public function load(ClassMetadata $meta, Component $component, $entity): bool
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
                $pattern = '/must be (of the type|an instance of) [a-zA-Z]+, null returned$/';
                if (!preg_match($pattern, $error->getMessage())) {
                    throw $error;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ClassMetadata $meta, Component $component, &$entity): bool
    {
        if (!$component instanceof BaseControl) {
            return false;
        }

        $name = $component->getName() ?: '';

        if ($meta->hasField($name) && $this->accessor->isWritable($entity, $name)) {
            $this->accessor->setValue($entity, $name, $component->getValue());
            return true;
        }

        return false;
    }
}
