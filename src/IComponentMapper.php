<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper;

use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\Exceptions\InvalidStateException;
use Nette\ComponentModel\IComponent;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
interface IComponentMapper
{
    public const ITEMS_TITLE = 'items.title';
    public const ITEMS_FILTER = 'items.filter';
    public const ITEMS_ORDER = 'items.order';

    /**
     * @param mixed $entity
     * @throws InvalidStateException
     */
    public function load(ClassMetadata $meta, IComponent $component, $entity): bool;

    /**
     * @param mixed $entity
     * @throws InvalidStateException
     */
    public function save(ClassMetadata $meta, IComponent $component, &$entity): bool;
}
