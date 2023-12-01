<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Tests\Integration\Mappers;

require __DIR__ . '/../../bootstrap.php';

use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\Mappers\Column;
use FreezyBee\DoctrineFormMapper\Mappers\Embedded;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Pond;
use FreezyBee\DoctrineFormMapper\Tests\Mock\EntityManagerTrait;
use Nette\Forms\Container;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
class EmbeddedTest extends TestCase
{
    use EntityManagerTrait;

    private function createMapper(): Embedded
    {
        $mapper = new DoctrineFormMapper($this->getEntityManager());
        $result = new Embedded($mapper);
        $mapper->addMapper($result);
        $mapper->addMapper(new Column($mapper));
        return $result;
    }

    public function testLoadAndSave(): void
    {
        $meta = $this->getEntityManager()->getClassMetadata(Pond::class);
        $pond = new Pond();

        $frogContainer = new Container();
        $frogContainer->setParent(new Container(), 'frog');
        $nameInput = $frogContainer->addText('name');

        // load
        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $frogContainer, $pond);
        Assert::true($result);

        // save
        $nameInput->setValue('test name');
        $result = $mapper->save($meta, $frogContainer, $pond);
        Assert::true($result);
        Assert::equal('test name', $pond->getFrog()->getName());
    }
}

(new EmbeddedTest())->run();
