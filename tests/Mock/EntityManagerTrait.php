<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\DoctrineFormMapper\Tests\Mock;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Repository\RepositoryFactory;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;

/**
 * Class ContainerTestCase
 * @package FreezyBee\DoctrineFormMapper\Tests
 */
trait EntityManagerTrait
{
    /** @var EntityManager */
    private $entityManager;

    /** @var string */
    private $dbFilename;

    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        if ($this->entityManager === null) {
            AnnotationRegistry::registerLoader('class_exists');

            $configuration = new Configuration;
            $configuration->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader));
            $configuration->setProxyDir(__DIR__ . '/../tmp');
            $configuration->setProxyNamespace('Proxy');

            /** @var RepositoryFactory $factory */
            $factory = new class implements RepositoryFactory {
                /**
                 * @param EntityManager|EntityManagerInterface $entityManager
                 * @param string $entityName
                 * @return EntityRepository
                 */
                public function getRepository(EntityManagerInterface $entityManager, $entityName)
                {
                    return new EntityRepository($entityManager, $entityManager->getClassMetadata($entityName));
                }
            };

            $configuration->setRepositoryFactory($factory);

            $this->dbFilename = $dbFilename = __DIR__ . '/../tmp/test' . random_int(1, 10000) . '.sqlite3';

            $connection = DriverManager::getConnection(['url' => 'sqlite:///' . $dbFilename], $configuration);
            $connection->exec(file_get_contents(__DIR__ . '/test.sql'));

            $this->entityManager = EntityManager::create($connection, $configuration);
        }

        return $this->entityManager;
    }

    /**
     * Remove db file
     */
    public function tearDown()
    {
        unlink($this->dbFilename);
    }
}
