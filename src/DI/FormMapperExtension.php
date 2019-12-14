<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\DI;

use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\Mappers\Column;
use FreezyBee\DoctrineFormMapper\Mappers\Construct;
use FreezyBee\DoctrineFormMapper\Mappers\Embedded;
use FreezyBee\DoctrineFormMapper\Mappers\ManyToMany;
use FreezyBee\DoctrineFormMapper\Mappers\ManyToOne;
use FreezyBee\DoctrineFormMapper\Mappers\OneToOne;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class FormMapperExtension extends CompilerExtension
{
    /** @var mixed[] */
    private $defaults = [
        'mappers' => [
            Construct::class,
            Column::class,
            OneToOne::class,
            Embedded::class,
            ManyToOne::class,
            ManyToMany::class,
        ],
        'entityManager' => null
    ];

    /**
     *
     */
    public function loadConfiguration(): void
    {
        $config = $this->validateConfig($this->defaults);

        $builder = $this->getContainerBuilder();

        $mapperDef = $builder
            ->addDefinition($this->prefix('doctrineFormMapper'))
            ->setFactory(DoctrineFormMapper::class);

        if ($config['entityManager']) {
            $mapperDef->setArguments([$config['entityManager']]);
        }

        foreach ($config['mappers'] as $mapperClass) {
            $mapper = $mapperClass instanceof Statement ? $mapperClass : new Statement($mapperClass);
            $mapperDef->addSetup('?->addMapper(?)', ['@self', $mapper]);
        }
    }
}
