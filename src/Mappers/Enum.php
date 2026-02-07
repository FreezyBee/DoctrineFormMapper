<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Mappers;

use BackedEnum;
use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use LogicException;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\ChoiceControl;
use Nette\SmartObject;
use ReflectionEnum;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
class Enum implements IComponentMapper
{
    use SmartObject;

    private PropertyAccessorInterface $accessor;

    public function __construct(DoctrineFormMapper $mapper)
    {
        $this->accessor = $mapper->getAccessor();
    }

    public function load(ClassMetadata $meta, IComponent $component, $entity): bool
    {
        if (!$component instanceof ChoiceControl) {
            return false;
        }

        $name = $component->getName() ?: '';

        $enumType = $meta->fieldMappings[$name]['enumType'] ?? null;
        if ($enumType === null) {
            return false;
        }

        $this->setDefaultItems($component, $enumType);

        // set default value
        try {
            $value = $this->accessor->getValue($entity, $name);
            $component->setDefaultValue($value);
        } catch (AccessException $e) {
        }

        return true;
    }

    public function save(ClassMetadata $meta, IComponent $component, &$entity): bool
    {
        if (!$component instanceof ChoiceControl) {
            return false;
        }

        $name = $component->getName() ?: '';

        $enumType = $meta->fieldMappings[$name]['enumType'] ?? null;
        if ($enumType === null) {
            return false;
        }

        if ($this->accessor->isWritable($entity, $name)) {
            try {
                $value = $enumType::from($component->getValue());
                $this->accessor->setValue($entity, $name, $value);
            } catch (LogicException $e) {
                return false;
            }

            return true;
        }

        return true;
    }

    /**
     * @param class-string<BackedEnum> $enumType
     */
    private function setDefaultItems(ChoiceControl $component, string $enumType): void
    {
        $items = [];

        $enumRef = new ReflectionEnum($enumType);
        if (!$enumRef->isBacked()) {
            throw new LogicException('Enum must be backed.');
        }

        $labelCallback = $component->getOption(IComponentMapper::ITEMS_TITLE);

        foreach ($enumRef->getCases() as $case) {
            $item = $case->getValue();
            assert($item instanceof BackedEnum);
            $items[$item->value] = is_callable($labelCallback) ? $labelCallback($item) : $item->name;
        }

        $component->setItems($items);
    }
}
