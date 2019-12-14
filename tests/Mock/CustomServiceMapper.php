<?php

declare(strict_types=1);

namespace FreezyBee\DoctrineFormMapper\Tests\Mock;

use Doctrine\ORM\Mapping\ClassMetadata;
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use Nette\ComponentModel\Component;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class CustomServiceMapper implements IComponentMapper
{
    /** @var DoctrineFormMapper */
    private $mapper;

    /** @var CustomService */
    private $customService;

    /** @var string */
    private $param;

    /**
     * @param DoctrineFormMapper $mapper
     * @param CustomService $customService
     * @param string $param
     */
    public function __construct(DoctrineFormMapper $mapper, CustomService $customService, string $param)
    {
        $this->mapper = $mapper;
        $this->customService = $customService;
        $this->param = $param;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ClassMetadata $meta, Component $component, $entity): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ClassMetadata $meta, Component $component, &$entity): bool
    {
        return false;
    }
}
