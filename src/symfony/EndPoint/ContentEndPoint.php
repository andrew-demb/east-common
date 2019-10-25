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

namespace Teknoo\East\WebsiteBundle\EndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\EndPoint\ContentEndPointTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ContentEndPoint implements EndPointInterface
{
    use EastEndPointTrait,
        ContentEndPointTrait;

    protected function parseUrl(ServerRequestInterface $request): array
    {
        $path = (string) $request->getUri()->getPath();
        $path = \str_replace(['app.php', 'app_dev.php'], '', $path);
        return \explode('/', \trim($path, '/'));
    }
}
