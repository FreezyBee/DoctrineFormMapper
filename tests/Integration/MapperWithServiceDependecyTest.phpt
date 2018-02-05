<?php
declare(strict_types=1);

namespace FreezyBee\DoctrineFormMapper\Tests\Integration;

require __DIR__ . '/../bootstrap.php';

use FreezyBee\DoctrineFormMapper\DoctrineFormMapper;
use Nette\Configurator;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
class MapperWithServiceDependecyTest extends TestCase
{
    /**
     *
     */
    public function testInjection()
    {
        // test load

        $configurator = new Configurator;
        $configurator->setTempDirectory(__DIR__ . '/../tmp');
        $configurator->addConfig(__DIR__ . '/../config.neon');
        $container = $configurator->createContainer();


        /** @var DoctrineFormMapper $mapper */
        $mapper = $container->getByType(DoctrineFormMapper::class);

        Assert::type(DoctrineFormMapper::class, $mapper);
    }
}

(new MapperWithServiceDependecyTest)->run();
