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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);


namespace Teknoo\East\Website\Object;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Media implements ObjectInterface, DeletableInterface
{
    use ObjectTrait;

    private string $name;

    private int $length;

    private string $mimeType;

    private string $alternative;

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function setName(string $name): Media
    {
        $this->name = $name;

        return $this;
    }

    public function getLength(): int
    {
        return (int) $this->length;
    }

    public function setLength(int $length): Media
    {
        $this->length = $length;

        return $this;
    }

    public function getMimeType(): string
    {
        return (string) $this->mimeType;
    }

    public function setMimeType(string $mimeType): Media
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getAlternative(): string
    {
        return (string) $this->alternative;
    }

    public function setAlternative(string $alternative): Media
    {
        $this->alternative = $alternative;

        return $this;
    }
}
