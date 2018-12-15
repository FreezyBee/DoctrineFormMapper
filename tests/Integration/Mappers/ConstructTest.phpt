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

    /** @var Construct */
    private $mapper;

    /**
     *
     */
    public function setUp()
    {
        $mapper = new DoctrineFormMapper($this->getEntityManager());
        $this->mapper = new Construct($mapper);
        $mapper->addMapper($this->mapper);
    }

    /**
     *
     */
    public function testLoad()
    {
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $component = new TextInput;
        $component->setParent(new Container, 'name');

        $result = $this->mapper->load($meta, $component, Article::class);
        Assert::false($result);
    }

    /**
     *
     */
    public function testSave()
    {
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $article = Article::class;

        $component = new \Nette\Forms\Container;
        $selectControl = $component->addSelect('author');

        $result = $this->mapper->load($meta, $component, $article);
        Assert::false($result);

        $selectControl->setItems([11 => '', 12 => '']);
        $selectControl->setValue(12);

        $result = $this->mapper->save($meta, $component, $article);
        Assert::false($result);
        Assert::true($article instanceof Article);

        /** @var Article $article */
        Assert::same(12, $article->getAuthor()->getId());
    }

    /**
     *
     */
    public function testExceptionMissingControl()
    {
        Assert::exception(function () {
            $author = Author::class;
            $meta = $this->getEntityManager()->getClassMetadata($author);
            $container = new \Nette\Forms\Container;
            $this->mapper->save($meta, $container, $author);
        }, InvalidStateException::class, "Can't create new instance: control 'name' is missing");
    }

    /**
     *
     */
    public function testCreateWithoutConstructor()
    {
        $tag = Tag::class;
        $meta = $this->getEntityManager()->getClassMetadata($tag);
        $container = new \Nette\Forms\Container;
        $this->mapper->save($meta, $container, $tag);
        Assert::true($tag instanceof Tag);
    }

    /**
     *
     */
    public function testCreateWithNonAssociatedObjectInConstructor()
    {
        /** @var TestDate $testDate */
        $testDate = TestDate::class;
        $meta = $this->getEntityManager()->getClassMetadata($testDate);
        $container = new \Nette\Forms\Container;
        $container->addComponent(new class extends BaseControl {
            public function getValue()
            {
                return new \DateTime('21.12.2012');
            }
        }, 'date');

        $this->mapper->save($meta, $container, $testDate);
        Assert::same('21.12.2012', $testDate->getDate()->format('d.m.Y'));
    }

    /**
     *
     */
    public function testRunNonContainerOrEntityObject()
    {
        $meta = $this->getEntityManager()->getClassMetadata(Tag::class);

        // test non class name
        $object = new \stdClass;
        $this->mapper->save($meta, new \Nette\Forms\Container, $object);
        Assert::equal(new \stdClass, $object);
    }
}

(new ConstructTest)->run();
