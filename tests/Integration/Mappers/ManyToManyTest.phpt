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
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use FreezyBee\DoctrineFormMapper\Mappers\ManyToMany;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Address;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Article;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Author;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Flag;
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

    private function createMapper(): ManyToMany
    {
        $mapper = new DoctrineFormMapper($this->getEntityManager());
        $result = new ManyToMany($mapper);
        $mapper->addMapper($result);
        return $result;
    }

    public function testLoad(): void
    {
        $em = $this->getEntityManager();
        $article = $em->find(Article::class, 101);
        $meta = $em->getClassMetadata(Article::class);

        $component = new MultiSelectBox();
        $component->setOption(IComponentMapper::ITEMS_TITLE, 'name');
        $component->setParent(new Container(), 'tags');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, $article);
        Assert::true($result);
        Assert::same([1001, 1002], $component->getValue());
    }

    public function testLoadItemsWithCriteriaCallback(): void
    {
        $em = $this->getEntityManager();
        $meta = $em->getClassMetadata(Article::class);

        $component = new MultiSelectBox();
        $component->setOption(IComponentMapper::ITEMS_TITLE, 'name');
        $component->setOption(IComponentMapper::ITEMS_FILTER, function (QueryBuilder $qb) {
            $qb->andWhere('entity.id = 1001');
        });
        $component->setParent(new Container(), 'tags');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, new Article(new Author('', new Address()), Flag::A));
        Assert::true($result);
        Assert::same([
            1001 => 'tag name1',
        ], $component->getItems());
    }

    public function testLoadNonExistsField(): void
    {
        $article = new Article(new Author('', new Address()), Flag::A);
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $component = new MultiSelectBox();
        $component->setParent(new Container(), 'namee');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, $article);
        Assert::false($result);
    }

    public function testSave(): void
    {
        $article = new Article(new Author('', new Address()), Flag::A);
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $testIds = [1003];

        $component = new MultiSelectBox();
        $component->setParent(new Container(), 'tags');
        $component->setItems([
            1001 => '',
            1002 => '',
            1003 => '',
            1004 => '',
        ]);
        $component->setValue($testIds);

        $mapper = $this->createMapper();
        $result = $mapper->save($meta, $component, $article);
        Assert::true($result);
        Assert::count(1, $article->getTags());

        /** @var Tag $tag */
        $tag = $article->getTags()->first();
        Assert::same(1003, $tag->getId());
        Assert::same('tag name3', $tag->getName());
    }

    public function testSaveWithoutAssociation(): void
    {
        $article = new Article(new Author('', new Address()), Flag::A);
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $component = new MultiSelectBox();
        $component->setParent(new Container(), 'tagss');

        $mapper = $this->createMapper();
        $result = $mapper->save($meta, $component, $article);
        Assert::false($result);
    }

    public function testRunNonMultiChoiseControl(): void
    {
        $tag = new Tag();
        $meta = $this->getEntityManager()->getClassMetadata(Tag::class);
        $input = new TextInput();

        $mapper = $this->createMapper();
        Assert::false($mapper->load($meta, $input, $tag));
        Assert::false($mapper->save($meta, $input, $tag));
    }
}

(new ManyToManyTest())->run();
