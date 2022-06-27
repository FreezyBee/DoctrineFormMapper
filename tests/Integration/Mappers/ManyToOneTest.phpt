<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Tests\Integration\Mappers;

require __DIR__ . '/../../bootstrap.php';

use Doctrine\ORM\QueryBuilder;
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\Exceptions\InvalidStateException;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use FreezyBee\DoctrineFormMapper\Mappers\ManyToOne;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Address;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Article;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Author;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Tag;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\UuidCart;
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
        $this->mapper = new ManyToOne($mapper);
        $mapper->addMapper($this->mapper);
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

    public function testLoadItemsWithCriteriaCallback()
    {
        $em = $this->getEntityManager();
        $meta = $em->getClassMetadata(Article::class);

        $component = new SelectBox;
        $component->setOption(IComponentMapper::ITEMS_TITLE, 'name');
        $component->setOption(IComponentMapper::ITEMS_FILTER, function (QueryBuilder $qb) {
            $qb->andWhere('entity.id = 11');
        });
        $component->setParent(new Container, 'author');
        $component->checkDefaultValue(false);

        $result = $this->mapper->load($meta, $component, new Article(new Author('', new Address())));
        Assert::true($result);
        Assert::same([11 => 'author name1'], $component->getItems());
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
    public function testLoadUuid()
    {
        $em = $this->getEntityManager();
        $cart = $em->find(UuidCart::class, '7ec0407c-e7da-48d7-80d6-3b98c4002c00');
        $meta = $em->getClassMetadata(UuidCart::class);

        $component = new SelectBox;
        $component->setOption(IComponentMapper::ITEMS_TITLE, 'name');
        $component->setParent(new Container, 'product');

        $result = $this->mapper->load($meta, $component, $cart);
        Assert::true($result);
        Assert::same('7ec0407c-e7da-48d7-80d6-3b98c4002c21', $component->getValue());
        Assert::same([
            '7ec0407c-e7da-48d7-80d6-3b98c4002c21' => 'product1',
            '7ec0407c-e7da-48d7-80d6-3b98c4002c22' => 'product2'
        ], $component->getItems());
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
