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
use FreezyBee\DoctrineFormMapper\Mappers\OneToOne;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Address;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Article;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Author;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Car;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Tag;
use FreezyBee\DoctrineFormMapper\Tests\Mock\EntityManagerTrait;
use Nette\ComponentModel\Container;
use Nette\Forms\Controls\TextInput;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
class OneToOneTest extends TestCase
{
    use EntityManagerTrait;

    private function createMapper(): OneToOne
    {
        $mapper = new DoctrineFormMapper($this->getEntityManager());
        $result = new OneToOne($mapper);
        $mapper->addMapper(new Column($mapper));
        $mapper->addMapper($result);
        return $result;
    }

    public function testLoad(): void
    {
        $em = $this->getEntityManager();
        $author = $em->find(Author::class, 11);
        $meta = $em->getClassMetadata(Author::class);

        $component = new \Nette\Forms\Container();
        $component->setParent(new Container(), 'address');
        $textControl = $component->addText('street');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, $author);
        Assert::true($result);
        Assert::same('address street1', $textControl->getValue());
    }

    public function testLoadNonExistsField(): void
    {
        $article = new Author('', new Address());
        $meta = $this->getEntityManager()->getClassMetadata(Article::class);

        $component = new \Nette\Forms\Container();
        $component->setParent(new Container(), 'address');
        $component->addText('streets');

        $mapper = $this->createMapper();
        $result = $mapper->load($meta, $component, $article);
        Assert::false($result);
    }

    public function testSaveManaged(): void
    {
        $em = $this->getEntityManager();

        /** @var Author $author */
        $author = $em->find(Author::class, 11);
        $meta = $em->getClassMetadata(Author::class);

        $component = new \Nette\Forms\Container();
        $component->setParent(new Container(), 'address');
        $textControl = $component->addText('street');

        $mapper = $this->createMapper();
        $mapper->load($meta, $component, $author);
        Assert::same(1, $author->getAddress()->getId());

        $textControl->setValue('street name 3!!!');

        $result = $mapper->save($meta, $component, $author);
        Assert::true($result);

        $em->flush();
        $em->clear();

        $author = $em->find(Author::class, 11);
        Assert::same('street name 3!!!', $author?->getAddress()->getStreet());
    }

    public function testSaveNonRelated(): void
    {
        $author = new Author('x', new Address());
        $meta = $this->getEntityManager()->getClassMetadata(Author::class);

        $component = new \Nette\Forms\Container();
        $component->setParent(new Container(), 'addresss');
        $component->addText('street');

        $mapper = $this->createMapper();
        $result = $mapper->save($meta, $component, $author);
        Assert::false($result);
    }

    public function testSaveNewInstance(): void
    {
        $author = new Author('x', new Address());
        $meta = $this->getEntityManager()->getClassMetadata(Author::class);

        $component = new \Nette\Forms\Container();
        $component->setParent(new Container(), 'car');
        $component->addText('name');

        $mapper = $this->createMapper();
        $result = $mapper->save($meta, $component, $author);
        Assert::true($result);
        Assert::true($author->getCar() instanceof Car);
    }

    public function testRunNonContainer(): void
    {
        $tag = new Tag();
        $meta = $this->getEntityManager()->getClassMetadata(Tag::class);
        $input = new TextInput();

        $mapper = $this->createMapper();
        Assert::false($mapper->load($meta, $input, $tag));
        Assert::false($mapper->save($meta, $input, $tag));
    }
}

(new OneToOneTest())->run();
