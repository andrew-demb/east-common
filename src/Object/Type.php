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

namespace Teknoo\East\Website\Object;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Type implements ObjectInterface, DeletableInterface, TimestampableInterface
{
    use ObjectTrait;

    private string $name = '';

    private string $template = '';

    /**
     * @var array<string, string>
     */
    private array $blocks = [];

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function setName(string $name): Type
    {
        $this->name = $name;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getTemplate(): string
    {
        return (string) $this->template;
    }

    public function setTemplate(string $template): Type
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return array<Block>
     */
    public function getBlocks(): array
    {
        return \array_map(
            static function ($key, $value) {
                return new Block($key, $value);
            },
            \array_keys($this->blocks),
            \array_values($this->blocks)
        );
    }

    /**
     * @param array<Block> $blocks
     */
    public function setBlocks(array $blocks): Type
    {
        $this->blocks = [];

        foreach ($blocks as $block) {
            $this->blocks[$block->getName()] = $block->getType();
        }

        return $this;
    }
}
