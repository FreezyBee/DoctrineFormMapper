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

With Nette `2.4` and newer, you can enable the extension using your neon config.

```yml
extensions:
	formMapper: FreezyBee\DoctrineFormMapper\DI\FormMapperExtension
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
                ->setOption(IComponentMapper::ITEMS_FILTER, ['age !=' => 0])
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
    
    
            // 1.) create new entity
            $article = new Article;
    
            // 2.) load entity from db
            $article = $this->articlesRepository->find(1);
            
            // 3.) create new entity by class name - see point 4
            $article = Article::class;
            
            // load data from entity to form
            $this->mapper->load($article, $form);
    
            $form->onSuccess[] = function (Form $form) use ($article) {
                
                // save data from form to entity - without flush!!!
                $this->mapper->save($article, $form);
                
                // 4.) if article was classname, mapper create new instance and
                // in variable article you get entity
                // $article is instanceof Article
                
                // flush data...
                $this->mapper->getEntityManager()->persist($article)->flush();
            };
    
            return $form;
        }
    }
```