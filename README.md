Quickstart
==========

[![Build Status](https://travis-ci.org/FreezyBee/DoctrineFormMapper.svg?branch=master)](https://travis-ci.org/FreezyBee/DoctrineFormMapper)
[![Coverage Status](https://coveralls.io/repos/github/FreezyBee/DoctrineFormMapper/badge.svg?branch=master)](https://coveralls.io/github/FreezyBee/DoctrineFormMapper?branch=master)

Installation
------------

The best way to install FreezyBee/DoctrineFormMapper is using  [Composer](http://getcomposer.org/):

```sh
$ composer require freezy-bee/doctrine-form-mapper
```

config.neon

```yml
extensions:
    formMapper: FreezyBee\DoctrineFormMapper\DI\FormMapperExtension

# optional settings
formMapper:
    mappers:
        - CustomMapper()
    entityManager: @customEntityManager # default autowired EntityManagerInterface
```

Extension is responsible for registering service FreezyBee\DoctrineFormMapper\DoctrineFormMapper, you can autowire it.

Documentation
-------------

### Usage

```php
use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use FreezyBee\DoctrineFormMapper\IComponentMapper;

class XPresenter extends Presenter
{
    /** @var DoctrineFormMapper @inject */
    public $mapper;

    /** @var EntityRepository @inject */
    public $articlesRepository;
    
    protected function createComponentForm()
    {
        $form = new Form;

        // Column
        $form->addText('name');

        // ManyToOne
        $form->addSelect('author')
            // order items
            ->setOption(IComponentMapper::ITEMS_ORDER, ['age' => 'ASC'])
            // filter items
            ->setOption(IComponentMapper::ITEMS_FILTER, ['age' => 0])
            // filter items by callback
            ->setOption(IComponentMapper::ITEMS_FILTER, function(QueryBuilder $qb) {
                $qb->andWhere('entity.age != 0')
            })
            // custom select label renderer
            ->setOption(IComponentMapper::ITEMS_TITLE, function (Author $author) {
                return $author->getName() . ' - ' . $author->getAge();
            });

        // ManyToOne
        $form->addRadioList('tags')
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name');


        // ManyToMany
        $form->addMultiSelect('users')
            ->setOption(IComponentMapper::ITEMS_TITLE, 'username');

        // ManyToMany
        // btw you can define items and then ITEMS_TITLE is not required
        $form->addCheckboxList('countries', 'Countries', [1 => 'CZ', 2 => 'SK']);


        // A) create new entity
        $article = new Article;

        // B) load entity from db
        $article = $this->articlesRepository->find(1);
        
        // C) create new entity by class name - see point INFO below
        $article = Article::class;
        
        // load data from entity to form
        $this->mapper->load($article, $form);

        $form->onSuccess[] = function (Form $form) use ($article) {
            
            // save (map) data from form to entity - without flush!!!
            $articleEntity = $this->mapper->save($article, $form);
            
            // INFO - if article was classname, mapper create new instance
            // $articleEntity is instanceof Article
            
            // flush data...
            $em = $this->mapper->getEntityManager();
            $em->persist($articleEntity)
            $em->flush();
        };

        return $form;
    }
}
```
