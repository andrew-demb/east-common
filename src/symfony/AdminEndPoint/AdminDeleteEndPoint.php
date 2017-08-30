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

namespace Teknoo\East\WebsiteBundle\AdminEndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\Service\DeletingService;

class AdminDeleteEndPoint implements EndPointInterface
{
    use EastEndPointTrait,
        AdminEntPointTrait;

    /**
     * @var DeletingService
     */
    private $deletingService;

    /**
     * @param DeletingService $deletingService
     * @return AdminDeleteEndPoint
     */
    public function setDeletingService(DeletingService $deletingService): AdminDeleteEndPoint
    {
        $this->deletingService = $deletingService;

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClientInterface $client
     * @param string $id
     * @param string $redirection
     * @return self
     */
    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        string $id,
        string $redirection
    ) {
        $this->loader->load(
            ['id' => $id],
            new Promise(
                function ($object) use ($client, $redirection) {
                    $this->deletingService->delete($object);

                    $this->redirect($client, $redirection);
                },
                function ($throwable) use ($client) {
                    $client->errorInRequest($throwable);
                }
            )
        );

        return $this;
    }
}
