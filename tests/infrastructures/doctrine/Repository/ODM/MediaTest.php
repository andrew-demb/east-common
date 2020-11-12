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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Doctrine\Repository\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
use MongoDB\BSON\ObjectId;
use MongoDB\GridFS\Bucket;
use MongoDB\GridFS\Exception\FileNotFoundException;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Doctrine\Repository\ODM\Media;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Repository\ODM\Media
 */
class MediaTest extends TestCase
{
    private ?DocumentManager $dm = null;

    private ?UnitOfWork $uow = null;

    private ?ClassMetadata $class = null;

    /**
     * @return DocumentManager|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getDocumentManager(): DocumentManager
    {
        if (!$this->dm instanceof DocumentManager) {
            $this->dm = $this->createMock(DocumentManager::class);
        }

        return $this->dm;
    }

    /**
     * @return UnitOfWork|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getUnitOfWork(): UnitOfWork
    {
        if (!$this->uow instanceof UnitOfWork) {
            $this->uow = $this->createMock(UnitOfWork::class);
        }

        return $this->uow;
    }

    /**
     * @return ClassMetadata|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getClassMetadata(): ClassMetadata
    {
        if (!$this->class instanceof ClassMetadata) {
            $this->class = $this->createMock(ClassMetadata::class);
            $this->class->name = 'fooBar';
        }

        return $this->class;
    }

    public function buildRepository()
    {
        return new Media(
            $this->getDocumentManager(),
            $this->getUnitOfWork(),
            $this->getClassMetadata()
        );
    }

    public function testOpenDownloadStreamWithLegacyId()
    {
        if (!\class_exists(ObjectId::class)) {
            $this->markTestSkipped('Mongodb is not available');
        }

        $id = 'IEdJQ4vbUO7UNyrlmjIZUoQWCW99TYPq';
        $bucket = $this->createMock(Bucket::class);
        $bucket->expects(self::any())
            ->method('openDownloadStream')
            ->with($id)
            ->willReturn(\fopen('php://memory', 'r'));

        $this->getClassMetadata()
            ->expects(self::any())
            ->method('getDatabaseIdentifierValue')
            ->willReturnCallback(fn ($id) => new ObjectId($id));

        $this->getDocumentManager()
            ->expects(self::any())
            ->method('getDocumentBucket')
            ->willReturn($bucket);

        self::assertNotEmpty(
            $this->buildRepository()->openDownloadStream($id)
        );
    }

    public function testOpenDownloadStreamWithObjectId()
    {
        if (!\class_exists(ObjectId::class)) {
            $this->markTestSkipped('Mongodb is not available');
        }

        $id = '5f0f4a76c0918d70c7759a52';
        $bucket = $this->createMock(Bucket::class);
        $bucket->expects(self::any())
            ->method('openDownloadStream')
            ->with(new ObjectId($id))
            ->willReturn(\fopen('php://memory', 'r'));

        $this->getClassMetadata()
            ->expects(self::any())
            ->method('getDatabaseIdentifierValue')
            ->willReturnCallback(fn ($id) => new ObjectId($id));

        $this->getDocumentManager()
            ->expects(self::any())
            ->method('getDocumentBucket')
            ->willReturn($bucket);

        self::assertNotEmpty(
            $this->buildRepository()->openDownloadStream($id)
        );
    }

    public function testOpenDownloadStreamWithObjectIdException()
    {
        $bucket = $this->createMock(Bucket::class);
        $bucket->expects(self::any())
            ->method('openDownloadStream')
            ->willThrowException(new FileNotFoundException('foo'));

        $this->getDocumentManager()
            ->expects(self::any())
            ->method('getDocumentBucket')
            ->willReturn($bucket);

        $this->expectException(DocumentNotFoundException::class);
        self::assertNotEmpty(
            $this->buildRepository()->openDownloadStream('2MbSIZleD7tjslM4luOgN1ho')
        );
    }
}
