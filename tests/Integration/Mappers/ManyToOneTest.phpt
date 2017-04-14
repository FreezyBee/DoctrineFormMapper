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
use FreezyBee\DoctrineFormMapper\Mappers\ManyToOne;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Address;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Article;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Author;
use FreezyBee\DoctrineFormMapper\Tests\Mock\EntityManagerTrait;
use Nette\ComponentModel\Container;
use Nette\Forms\Controls\SelectBox;
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
        $this->mapper = new ManyToOne(new DoctrineFormMapper($this->getEntityManager()));
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
}

(new ManyToOneTest)->run();
