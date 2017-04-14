<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\DI;

use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use Nette\DI\CompilerExtension;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class FormMapperExtension extends CompilerExtension
{
    /**
     *
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('doctrineFormMapper'))
            ->setClass(DoctrineFormMapper::class);
    }
}
