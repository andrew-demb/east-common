<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Doctrine\DBSource\Common;

use Doctrine\Persistence\ObjectManager;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;

/**
 * Default implementation of `ManagerInterface` wrapping Doctrine's object manager,
 * following generic Doctrine interfaces.
 * Usable with ORM or ODM.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Manager implements ManagerInterface
{
    public function __construct(
        private ObjectManager $objectManager,
    ) {
    }

    public function persist(object $object): ManagerInterface
    {
        $this->objectManager->persist($object);

        return $this;
    }

    public function remove(object $object): ManagerInterface
    {
        $this->objectManager->remove($object);

        return $this;
    }

    public function flush(): ManagerInterface
    {
        $this->objectManager->flush();

        return $this;
    }
}
