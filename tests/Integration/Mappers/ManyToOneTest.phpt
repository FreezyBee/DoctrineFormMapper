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
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use FreezyBee\DoctrineFormMapper\Mappers\ManyToOne;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Address;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Article;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Author;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Tag;
use FreezyBee\DoctrineFormMapper\Tests\Mock\EntityManagerTrait;
use Nette\ComponentModel\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextInput;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
class ManyToOneTest extends TestCase
{
    use EntityManagerTrait;

    /** @var ManyToOne */
    private $mapper;

    /**
     *
     */
    public function setUp()
    {
        $mapper = new DoctrineFormMapper($this->getEntityManager());
        $mapper->addMapper(new ManyToOne($mapper));
        $this->mapper = new ManyToOne($mapper);
    }

    /**
     *
     */
    public function testLoad()
    {
        $em = $this->getEntityManager();
        $article = $em->find(Article::class, 101);
        $meta = $em->getClassMetadata(Article::class);

        $component = new SelectBox;
        $component->setOption(IComponentMapper::ITEMS_TITLE, 'name');
        $component->setParent(new Container, 'author');

        $result = $this->mapper->load($meta, $component, $article);
        Assert::true($result);
        Assert::same(11, $component->getValue());
        Assert::same([11 => 'author name1', 12 => 'author name2', 13 => 'author name3'], $component->getItems());
    }

    /**
     *
     */
    public function testLoadNonExistsField()
    {
        $article = new Article(new Author('', new Address));
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $component = new SelectBox;
        $component->setParent(new Container, 'namee');

        $result = $this->mapper->load($meta, $component, $article);
        Assert::false($result);
    }

    /**
     *
     */
    public function testLoadWithoutFieldName()
    {
        Assert::exception(function () {
            $article = new Article(new Author('', new Address));
            $meta = $this->getEntityManager()->getClassMetadata(Article::class);

            $component = new SelectBox;
            $component->setParent(new Container, 'author');

            $this->mapper->load($meta, $component, $article);
        }, InvalidStateException::class, 'Use IComponentMapper::ITEMS_TITLE to specify items title or callback');
    }

    /**
     *
     */
    public function testLoadWithoutFieldNamex()
    {
        Assert::exception(function () {
            $article = new Article(new Author('', new Address));
            $meta = $this->getEntityManager()->getClassMetadata(Article::class);

            $component = new SelectBox;
            $component->setParent(new Container, 'author');

            $this->mapper->load($meta, $component, $article);
        }, InvalidStateException::class, 'Use IComponentMapper::ITEMS_TITLE to specify items title or callback');
    }

    /**
     *
     */
    public function testSave()
    {
        $em = $this->getEntityManager();
        /** @var Article $article */
        $article = $em->find(Article::class, 101);
        $meta = $em->getClassMetadata(Article::class);

        $component = new SelectBox;
        $component->setOption(IComponentMapper::ITEMS_TITLE, 'name');
        $component->setParent(new Container, 'author');

        $this->mapper->load($meta, $component, $article);
        Assert::same(11, $article->getAuthor()->getId());

        $component->setValue(12);

        $result = $this->mapper->save($meta, $component, $article);
        Assert::true($result);
        Assert::same(12, $article->getAuthor()->getId());
    }

    /**
     *
     */
    public function testSaveWithoutAssociation()
    {
        $author = new Author('', new Address);
        $meta = $this->getEntityManager()->getClassMetadata(Author::class);

        $component = new SelectBox;
        $component->setParent(new Container, 'authors');

        $result = $this->mapper->save($meta, $component, $author);
        Assert::false($result);
    }

    /**
     *
     */
    public function testSaveNull()
    {
        $author = new Author('', new Address);
        $meta = $this->getEntityManager()->getClassMetadata(Author::class);

        $component = new SelectBox;
        $component->setParent(new Container, 'car');
        $component->setValue(null);

        $this->mapper->save($meta, $component, $author);
        Assert::null($author->getCar());
    }

    /**
     *
     */
    public function testRunNonChoiseControl()
    {
        $tag = new Tag;
        $meta = $this->getEntityManager()->getClassMetadata(Tag::class);
        $input = new TextInput;

        Assert::false($this->mapper->load($meta, $input, $tag));
        Assert::false($this->mapper->save($meta, $input, $tag));
    }
}

(new ManyToOneTest)->run();
