<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Tests\Integration;

require __DIR__ . '/../bootstrap.php';

use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\IComponentMapper;
use FreezyBee\DoctrineFormMapper\Mappers\Column;
use FreezyBee\DoctrineFormMapper\Mappers\Construct;
use FreezyBee\DoctrineFormMapper\Mappers\Embedded;
use FreezyBee\DoctrineFormMapper\Mappers\ManyToMany;
use FreezyBee\DoctrineFormMapper\Mappers\ManyToOne;
use FreezyBee\DoctrineFormMapper\Mappers\OneToOne;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Article;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Author;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\ImmutableThing;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Tag;
use FreezyBee\DoctrineFormMapper\Tests\Mock\EntityManagerTrait;
use Nette\Application\UI\Form;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
class DoctrineFormMapperTest extends TestCase
{
    use EntityManagerTrait;

    private function createMapper(): DoctrineFormMapper
    {
        $mapper = new DoctrineFormMapper($this->getEntityManager());
        $mapper->addMapper(new Construct($mapper));
        $mapper->addMapper(new Column($mapper));
        $mapper->addMapper(new OneToOne($mapper));
        $mapper->addMapper(new Embedded($mapper));
        $mapper->addMapper(new ManyToOne($mapper));
        $mapper->addMapper(new ManyToMany($mapper));
        return $mapper;
    }

    public function testLoadAndSave(): void
    {
        // test load

        /** @var Article $article */
        $article = $this->getEntityManager()->find(Article::class, 101);

        $form = new Form();
        $titleControl = $form->addText('title');
        $authorControl = $form->addSelect('author')
            ->setOption(IComponentMapper::ITEMS_ORDER, [
                'age' => 'ASC',
            ])
            ->setOption(IComponentMapper::ITEMS_FILTER, [
                'age' => [666, 665],
            ])
            ->setOption(IComponentMapper::ITEMS_TITLE, function (Author $author) {
                return $author->getName() . ' - ' . $author->getAge();
            });

        $tagsControl = $form->addMultiSelect('tags')
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name');

        $mapper = $this->createMapper();
        $mapper->load($article, $form);

        Assert::same('article title1', $titleControl->getValue());

        Assert::same(11, $authorControl->getValue());
        Assert::same([
            12 => 'author name2 - 665',
            11 => 'author name1 - 666',
        ], $authorControl->getItems());

        Assert::same([1001, 1002], $tagsControl->getValue());
        Assert::same(
            [
                1001 => 'tag name1',
                1002 => 'tag name2',
                1003 => 'tag name3',
                1004 => 'tag name4',
            ],
            $tagsControl->getItems()
        );

        // test save

        $titleControl->setValue('new title!!!');
        $authorControl->setValue(12);
        $tagsControl->setValue([1003, 1004]);

        $mapper->save($article, $form);

        Assert::same('new title!!!', $article->getTitle());
        Assert::same(12, $article->getAuthor()->getId());

        $tagIds = $article->getTags()->map(function (Tag $tag) {
            return $tag->getId();
        })->toArray();

        Assert::same([1003, 1004], $tagIds);
    }

    public function testLoadContainer(): void
    {
        /** @var Author $author */
        $author = $this->getEntityManager()->find(Author::class, 13);

        $form = new Form();
        $nameControl = $form->addText('name');
        $addressContainer = $form->addContainer('address');
        $streetControl = $addressContainer->addText('street');

        $mapper = $this->createMapper();
        $mapper->load($author, $form);

        Assert::same('author name3', $nameControl->getValue());
        Assert::same('address street3', $streetControl->getValue());
    }

    public function testLoadByClassName(): void
    {
        $article = Article::class;

        $form = new Form();
        $authorControl = $form->addSelect('author')
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name');
        $tagsControl = $form->addMultiSelect('tags')
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name');

        $mapper = $this->createMapper();
        $mapper->load($article, $form);

        Assert::same([
            11 => 'author name1',
            12 => 'author name2',
            13 => 'author name3',
        ], $authorControl->getItems());
        Assert::same([
            1001 => 'tag name1',
            'tag name2',
            'tag name3',
            'tag name4',
        ], $tagsControl->getItems());
    }

    public function testSaveContainer(): void
    {
        $form = new Form();
        $nameControl = $form->addText('name');
        $addressContainer = $form->addContainer('address');
        $streetControl = $addressContainer->addText('street');

        $nameControl->setValue('nameX');
        $streetControl->setValue('streetX');

        $mapper = $this->createMapper();
        $author = $mapper->save(Author::class, $form);
        Assert::true($author instanceof Author);

        /** @var Author $author */
        Assert::same('nameX', $author->getName());
        Assert::same('streetX', $author->getAddress()->getStreet());
    }

    public function testSaveImmutable(): void
    {
        $form = new Form();
        $control = $form->addText('text');
        $control->setValue('textX');

        $mapper = $this->createMapper();
        $immutableThing = $mapper->save(ImmutableThing::class, $form);
        Assert::true($immutableThing instanceof ImmutableThing);

        /** @var ImmutableThing $immutableThing */
        Assert::same('textX', $immutableThing->getText());
    }
}

(new DoctrineFormMapperTest())->run();
