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

namespace Teknoo\East\Website\Service;

use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\SluggableInterface;
use Teknoo\East\Website\Query\FindBySlugQuery;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class FindSlugService
{
    private function sluggify(string $text): string
    {
        return \strtolower(\trim((string) \preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
    }

    public function process(
        LoaderInterface $loader,
        string $slugField,
        SluggableInterface $sluggable,
        array $parts,
        string $glue = '-'
    ): self {
        $counter = 1;
        $candidateAccepted = false;
        do {
            $candidateParts = $parts;
            if ($counter > 1) {
                $candidateParts[] = $counter;
            }

            $candidate = \implode($glue, \array_map([$this, 'sluggify'], $candidateParts));

            $loader->query(
                new FindBySlugQuery($slugField, $candidate),
                new Promise(
                    function () use (&$counter) {
                        $counter++;
                    },
                    function () use ($sluggable, $candidate, &$candidateAccepted) {
                        $sluggable->setSlug($candidate);
                        $candidateAccepted = true;
                    }
                )
            );
        } while (false === $candidateAccepted);

        return $this;
    }
}
