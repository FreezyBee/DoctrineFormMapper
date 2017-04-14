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
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Article;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Author;
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

    /** @var DoctrineFormMapper */
    private $mapper;

    /**
     *
     */
    public function setUp()
    {
        $this->mapper = new DoctrineFormMapper($this->getEntityManager());
    }

    /**
     *
     */
    public function testLoadAndSave()
    {
        // test load

        /** @var Article $article */
        $article = $this->getEntityManager()->find(Article::class, 101);

        $form = new Form;
        $titleControl = $form->addText('title');
        $authorControl = $form->addSelect('author')
            ->setOption(IComponentMapper::ITEMS_ORDER, ['age' => 'ASC'])
            ->setOption(IComponentMapper::ITEMS_FILTER, ['age !=' => 0])
            ->setOption(IComponentMapper::ITEMS_TITLE, function (Author $author) {
                return $author->getName() . ' - ' . $author->getAge();
            });

        $tagsControl = $form->addMultiSelect('tags')
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name');

        $this->mapper->load($article, $form);

        Assert::same('article title1', $titleControl->getValue());

        Assert::same(11, $authorControl->getValue());
        Assert::same([12 => 'author name2 - 665', 11 => 'author name1 - 666'], $authorControl->getItems());

        Assert::same([1001, 1002], $tagsControl->getValue());
        Assert::same(
            [1001 => 'tag name1', 1002 => 'tag name2', 1003 => 'tag name3', 1004 => 'tag name4'],
            $tagsControl->getItems()
        );


        // test save

        $titleControl->setValue('new title!!!');
        $authorControl->setValue(12);
        $tagsControl->setValue([1003, 1004]);

        $this->mapper->save($article, $form);

        Assert::same('new title!!!', $article->getTitle());
        Assert::same(12, $article->getAuthor()->getId());

        $tagIds = $article->getTags()->map(function (Tag $tag) {
            return $tag->getId();
        })->toArray();

        Assert::same([1003, 1004], $tagIds);
    }
}

(new DoctrineFormMapperTest)->run();
