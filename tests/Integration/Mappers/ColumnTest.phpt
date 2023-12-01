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
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Tag;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\TestDate;
use FreezyBee\DoctrineFormMapper\Tests\Mock\EntityManagerTrait;
use Nette\ComponentModel\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
class ColumnTest extends TestCase
{
    use EntityManagerTrait;

    private function createMapper(): Column
    {
        $mapper = new DoctrineFormMapper($this->getEntityManager());
        $result = new Column($mapper);
        $mapper->addMapper($result);
        return $result;
    }

    public function testLoad(): void
    {
        $em = $this->getEntityManager();

        $tag = $em->find(Tag::class, 1001);
        $meta = $em->getClassMetadata(Tag::class);

        $component = new TextInput();
        $component->setParent(new Container(), 'name');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, $tag);
        Assert::true($result);
        Assert::same('tag name1', $component->getValue());
    }

    public function testLoadNullableScalar(): void
    {
        $tag = new Tag();
        $meta = $this->getEntityManager()->getClassMetadata(Tag::class);

        $component = new TextInput();
        $component->setParent(new Container(), 'name');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, $tag);
        Assert::true($result);
        Assert::same('', $component->getValue());
    }

    public function testLoadNullableObject(): void
    {
        $classReflection = new \ReflectionClass(TestDate::class);
        $testDate = $classReflection->newInstanceWithoutConstructor();
        $meta = $this->getEntityManager()->getClassMetadata(TestDate::class);

        $component = new TextInput();
        $component->setParent(new Container(), 'date');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, $testDate);
        Assert::true($result);
        Assert::same('', $component->getValue());
    }

    public function testLoadNonExistsField(): void
    {
        $tag = new Tag();
        $meta = $this->getEntityManager()->getClassMetadata(Tag::class);

        $component = new TextInput();
        $component->setParent(new Container(), 'namee');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, $tag);
        Assert::false($result);
    }

    public function testSave(): void
    {
        $tag = new Tag();
        $meta = $this->getEntityManager()->getClassMetadata(Tag::class);

        $testName = 'nameY';

        $component = new TextInput();
        $component->setParent(new Container(), 'name');
        $component->setValue($testName);

        $mapper = $this->createMapper();
        $result = $mapper->save($meta, $component, $tag);
        Assert::true($result);
        Assert::same($testName, $tag->getName());
    }

    public function testRunWithButton(): void
    {
        $tag = new Tag();
        $meta = $this->getEntityManager()->getClassMetadata(Tag::class);
        $btn = new SubmitButton();

        $mapper = $this->createMapper();
        Assert::false($mapper->load($meta, $btn, $tag));
        Assert::false($mapper->save($meta, $btn, $tag));
    }
}

(new ColumnTest())->run();
