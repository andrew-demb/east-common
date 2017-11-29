<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Loader;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\MongoDbCollectionLoaderTrait;
use Teknoo\East\Website\Object\Media;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Loader\MediaLoader;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\Loader\MediaLoader
 * @covers      \Teknoo\East\Website\Loader\PublishableLoaderTrait
 * @covers      \Teknoo\East\Website\Loader\CollectionLoaderTrait
 * @covers      \Teknoo\East\Website\Loader\MongoDbCollectionLoaderTrait
 */
class MediaLoaderTest extends \PHPUnit\Framework\TestCase
{
    use LoaderTestTrait;

    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ObjectRepository
     */
    public function getRepositoryMock(): ObjectRepository
    {
        if (!$this->repository instanceof ObjectRepository) {
            $this->repository = $this->createMock(DocumentRepository::class);
        }

        return $this->repository;
    }

    /**
     * @return LoaderInterface|MediaLoader
     */
    public function buildLoader(): LoaderInterface
    {
        $repository = $this->getRepositoryMock();
        return new class($repository) extends MediaLoader {
            use MongoDbCollectionLoaderTrait;
        };
    }

    /**
     * @return LoaderInterface|MediaLoader
     */
    public function buildLoaderWithBadCollectionImplementation(): LoaderInterface
    {
        $repository = $this->getRepositoryMock();
        return new class($repository) extends MediaLoader {
            protected function prepareQuery(
                array &$criteria,
                ?array $order,
                ?int $limit,
                ?int $offset
            ) {
                return [];
            }
        };
    }

    /**
     * @return LoaderInterface|MediaLoader
     */
    public function buildLoaderWithNotCollectionImplemented(): LoaderInterface
    {
        $repository = $this->getRepositoryMock();
        return new MediaLoader($repository);
    }

    /**
     * @return Media
     */
    public function getEntity()
    {
        return new Media();
    }
    
    public function testByIdNotFound()
    {
        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findOneBy')
            ->with([
                'id' => 'foo',
                'deletedAt'=>null,
            ])
            ->willReturn(null);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::never())->method('success');
        $promiseMock->expects(self::once())
            ->method('fail')
            ->with($this->callback(function ($exception) {
                return $exception instanceof \DomainException;
            }));

        self::assertInstanceOf(
            MediaLoader::class,
            $this->buildLoader()->byId('foo', $promiseMock)
        );
    }

    public function testByIdFound()
    {
        $entity = $this->getEntity();

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findOneBy')
            ->with([
                'id' => 'foo',
                'deletedAt'=>null,
            ])
            ->willReturn($entity);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::once())->method('success')->with($entity);
        $promiseMock->expects(self::never())->method('fail');

        self::assertInstanceOf(
            MediaLoader::class,
            $this->buildLoader()->byId('foo', $promiseMock)
        );
    }
}
