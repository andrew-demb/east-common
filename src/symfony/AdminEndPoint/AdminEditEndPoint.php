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

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class AdminEditEndPoint implements EndPointInterface
{
    use EastEndPointTrait,
        AdminEntPointTrait,
        AdminFormTrait;

    /**
     * @param ServerRequestInterface $request
     * @param ClientInterface $client
     * @param string $id
     * @param bool $isTranslatable
     * @return self
     */
    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        string $id,
        bool $isTranslatable=false
    ) {
        $this->loader->load(
            ['id' => $id],
            new Promise(
                function ($object) use ($client, $request, $isTranslatable) {
                    $form = $this->createForm($object);
                    $form->handleRequest($request->getAttribute('request'));

                    if ($form->isSubmitted() && $form->isValid()) {
                        $this->writer->save($object, new Promise(
                            function ($object) use ($client, $form, $request, $isTranslatable) {
                                $this->render(
                                    $client,
                                    $this->viewPath,
                                    [
                                        'objectInstance' => $object,
                                        'formView' => $form->createView(),
                                        'request' => $request,
                                        'isTranslatable' => $isTranslatable
                                    ]
                                );
                            },
                            function ($error) use ($client) {
                                $client->errorInRequest($error);
                            }
                        ));

                        return;
                    }

                    $this->render(
                        $client,
                        $this->viewPath,
                        [
                            'objectInstance' => $object,
                            'formView' => $form->createView(),
                            'request' => $request,
                            'isTranslatable' => $isTranslatable
                        ]
                    );
                },
                function ($error) use ($client) {
                    $client->errorInRequest($error);
                }
            )
        );

        return $this;
    }
}
