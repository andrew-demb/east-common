<?php

/*
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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\ObjectManager;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\Object\TranslatableInterface;

interface AdapterInterface extends ManagerInterface
{
    public function getRootObject(): ObjectManager;

    public function getClassMetadata(string $class): ClassMetadata;

    public function getObjectChangeSet(TranslatableInterface $object): array;

    public function recomputeSingleObjectChangeSet(ClassMetadata $meta, TranslatableInterface $object): void;

    public function getScheduledObjectUpdates(): array;

    public function getScheduledObjectInsertions(): array;

    public function getScheduledObjectDeletions(): array;

    /**
     * @param mixed $value
     */
    public function setOriginalObjectProperty(string $oid, string $property, $value): void;

    public function computeChangeSet(ClassMetadata $class, object $object) : void;
}
