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
use FreezyBee\DoctrineFormMapper\Mappers\Construct;
use FreezyBee\DoctrineFormMapper\Tests\Mock\Entity\Article;
use FreezyBee\DoctrineFormMapper\Tests\Mock\EntityManagerTrait;
use Nette\Application\UI\Form;
use Nette\ComponentModel\Container;
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
        $this->mapper = new Construct(new DoctrineFormMapper($this->getEntityManager()));
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

        $component = new Form;
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
}

(new ConstructTest)->run();
