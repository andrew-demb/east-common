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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Object\PublishableInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Foundation\Manager\ManagerInterface;

use function is_callable;

/**
 * Recipe step to use into a HTTP EndPoint Recipe to create a form instance and handle the current request.
 * The form must be put into the manager's workplan.
 * If the key `publish` is present into the request, the current date will be passed to the object as published date.
 * Symfony implementation for `FormHandlingInterface`.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class FormHandling implements FormHandlingInterface
{
    public function __construct(
        private DatesService $datesService,
        private FormFactoryInterface $formFactory
    ) {
    }

    /**
     * @param array<string, mixed> $options
     * @return FormInterface<mixed>
     */
    private function createForm(
        string $formClass,
        ObjectInterface $data,
        array $options = array()
    ): FormInterface {
        return $this->formFactory->create($formClass, $data, $options);
    }

    public function __invoke(
        ServerRequestInterface $request,
        ManagerInterface $manager,
        string $formClass,
        array $formOptions,
        ObjectInterface $object
    ): FormHandlingInterface {
        $parsedBody = (array) $request->getParsedBody();
        if (
            $object instanceof PublishableInterface
            && isset($parsedBody['publish'])
            && is_callable([$object, 'setPublishedAt'])
        ) {
            $this->datesService->passMeTheDate($object->setPublishedAt(...));
        }

        $form = $this->createForm($formClass, $object, $formOptions);
        $form->handleRequest($request->getAttribute('request'));

        $manager->updateWorkPlan([
            'form' => $form,
        ]);

        return $this;
    }
}
