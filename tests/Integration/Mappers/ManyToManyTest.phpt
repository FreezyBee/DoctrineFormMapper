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
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use FreezyBee\DoctrineFormMapper\Mappers\ManyToMany;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Address;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Article;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Author;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Tag;
use FreezyBee\DoctrineFormMapper\Tests\Mock\EntityManagerTrait;
use Nette\ComponentModel\Container;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\TextInput;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
class ManyToManyTest extends TestCase
{
    use EntityManagerTrait;

    /** @var ManyToMany */
    private $mapper;

    /**
     *
     */
    public function setUp()
    {
        $mapper = new DoctrineFormMapper($this->getEntityManager());
        $mapper->addMapper(new ManyToMany($mapper));
        $this->mapper = new ManyToMany($mapper);
    }

    /**
     *
     */
    public function testLoad()
    {
        $em = $this->getEntityManager();
        $article = $em->find(Article::class, 101);
        $meta = $em->getClassMetadata(Article::class);

        $component = new MultiSelectBox;
        $component->setOption(IComponentMapper::ITEMS_TITLE, 'name');
        $component->setParent(new Container, 'tags');

        $result = $this->mapper->load($meta, $component, $article);
        Assert::true($result);
        Assert::same([1001, 1002], $component->getValue());
    }

    /**
     *
     */
    public function testLoadNonExistsField()
    {
        $article = new Article(new Author('', new Address));
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $component = new MultiSelectBox;
        $component->setParent(new Container, 'namee');

        $result = $this->mapper->load($meta, $component, $article);
        Assert::false($result);
    }

    /**
     *
     */
    public function testSave()
    {
        $article = new Article(new Author('', new Address));
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $testIds = [1003];

        $component = new MultiSelectBox;
        $component->setParent(new Container, 'tags');
        $component->setItems([1001 => '', 1002 => '', 1003 => '', 1004 => '']);
        $component->setValue($testIds);

        $result = $this->mapper->save($meta, $component, $article);
        Assert::true($result);
        Assert::count(1, $article->getTags());

        /** @var Tag $tag */
        $tag = $article->getTags()->first();
        Assert::same(1003, $tag->getId());
        Assert::same('tag name3', $tag->getName());
    }

    /**
     *
     */
    public function testSaveWithoutAssociation()
    {
        $article = new Article(new Author('', new Address));
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $component = new MultiSelectBox;
        $component->setParent(new Container, 'tagss');

        $result = $this->mapper->save($meta, $component, $article);
        Assert::false($result);
    }

    /**
     *
     */
    public function testRunNonMultiChoiseControl()
    {
        $tag = new Tag;
        $meta = $this->getEntityManager()->getClassMetadata(Tag::class);
        $input = new TextInput;

        Assert::false($this->mapper->load($meta, $input, $tag));
        Assert::false($this->mapper->save($meta, $input, $tag));
    }
}

(new ManyToManyTest)->run();
