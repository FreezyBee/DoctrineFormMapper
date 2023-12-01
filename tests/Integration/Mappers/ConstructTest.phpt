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
use FreezyBee\DoctrineFormMapper\Exceptions\InvalidStateException;
use FreezyBee\DoctrineFormMapper\Mappers\Construct;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Article;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Author;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Tag;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\TestDate;
use FreezyBee\DoctrineFormMapper\Tests\Mock\EntityManagerTrait;
use Nette\ComponentModel\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
class ConstructTest extends TestCase
{
    use EntityManagerTrait;

    private function createMapper(): Construct
    {
        $mapper = new DoctrineFormMapper($this->getEntityManager());
        $result = new Construct($mapper);
        $mapper->addMapper($result);
        return $result;
    }

    public function testLoad(): void
    {
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $component = new TextInput();
        $component->setParent(new Container(), 'name');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, Article::class);
        Assert::false($result);
    }

    public function testSave(): void
    {
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $article = Article::class;

        $component = new \Nette\Forms\Container();
        $selectControl = $component->addSelect('author');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, $article);
        Assert::false($result);

        $selectControl->setItems([
            11 => '',
            12 => '',
        ]);
        $selectControl->setValue(12);

        $result = $mapper->save($meta, $component, $article);
        Assert::false($result);
        Assert::true($article instanceof Article);

        /** @var Article $article */
        Assert::same(12, $article->getAuthor()->getId());
    }

    public function testExceptionMissingControl(): void
    {
        Assert::exception(function () {
            $author = Author::class;
            $meta = $this->getEntityManager()->getClassMetadata($author);
            $container = new \Nette\Forms\Container();
            $mapper = $this->createMapper();
            $mapper->save($meta, $container, $author);
        }, InvalidStateException::class, "Can't create new instance: control 'name' is missing");
    }

    public function testCreateWithoutConstructor(): void
    {
        $tag = Tag::class;
        $meta = $this->getEntityManager()->getClassMetadata($tag);
        $container = new \Nette\Forms\Container();
        $mapper = $this->createMapper();
        $mapper->save($meta, $container, $tag);
        Assert::true($tag instanceof Tag);
    }

    public function testCreateWithNonAssociatedObjectInConstructor(): void
    {
        $meta = $this->getEntityManager()->getClassMetadata(TestDate::class);
        $container = new \Nette\Forms\Container();
        $container->addComponent(new class() extends BaseControl {
            public function getValue()
            {
                return new \DateTime('21.12.2012');
            }
        }, 'date');

        $testDate = TestDate::class;
        $mapper = $this->createMapper();
        $mapper->save($meta, $container, $testDate);
        Assert::true($testDate instanceof TestDate);
        Assert::same('21.12.2012', $testDate->getDate()->format('d.m.Y'));
    }

    public function testRunNonContainerOrEntityObject(): void
    {
        $meta = $this->getEntityManager()->getClassMetadata(Tag::class);

        // test non class name
        $object = new \stdClass();
        $mapper = $this->createMapper();
        $mapper->save($meta, new \Nette\Forms\Container(), $object);
        Assert::equal(new \stdClass(), $object);
    }
}

(new ConstructTest())->run();
