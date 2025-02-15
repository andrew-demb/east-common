<?php

/*
 * East Common.
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

namespace Teknoo\East\Common\Recipe\Step;

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Service\FindSlugService;

/**
 * Recipe step to prepare a persisted and sluggable object to generate a new uniq slug (if needed) and inject it into
 * the object before save in a next step.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class SlugPreparation
{
    public function __construct(
        private FindSlugService $findSlugService,
    ) {
    }

    /**
     * @param LoaderInterface<SluggableInterface<ObjectInterface>> $loader
     */
    public function __invoke(LoaderInterface $loader, ObjectInterface $object, ?string $slugField = null): self
    {
        if (!$object instanceof SluggableInterface || null === $slugField) {
            return $this;
        }

        /** @var SluggableInterface<\Teknoo\East\Common\Contracts\Object\ObjectInterface> $object */
        $object->prepareSlugNear(
            $loader,
            $this->findSlugService,
            $slugField
        );

        return $this;
    }
}
