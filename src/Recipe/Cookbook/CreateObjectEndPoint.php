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

namespace Teknoo\East\Common\Recipe\Cookbook;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\CreateObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\SlugPreparation;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * HTTP EndPoint Recipe able to create a new persisted object implementing the class
 * `Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface`.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class CreateObjectEndPoint implements CreateObjectEndPointInterface
{
    use BaseCookbookTrait;

    /**
     * @param array<string, string> $createObjectWiths
     */
    public function __construct(
        RecipeInterface $recipe,
        private CreateObject $createObject,
        private FormHandlingInterface $formHandling,
        private FormProcessingInterface $formProcessing,
        private ?SlugPreparation $slugPreparation,
        private SaveObject $saveObject,
        private RedirectClientInterface $redirectClient,
        private RenderFormInterface $renderForm,
        private RenderError $renderError,
        private ?ObjectAccessControlInterface $objectAccessControl = null,
        private ?string $defaultErrorTemplate = null,
        private array $createObjectWiths = [],
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient(WriterInterface::class, 'writer'));
        $recipe = $recipe->require(new Ingredient('string', 'route'));
        $recipe = $recipe->require(new Ingredient('string', 'objectClass'));
        $recipe = $recipe->require(new Ingredient('string', 'formClass'));
        $recipe = $recipe->require(new Ingredient('array', 'formOptions'));
        $recipe = $recipe->require(new Ingredient('string', 'template'));

        $recipe = $recipe->cook($this->createObject, CreateObject::class, $this->createObjectWiths, 10);

        $recipe = $recipe->cook($this->formHandling, FormHandlingInterface::class, [], 20);

        $recipe = $recipe->cook($this->formProcessing, FormProcessingInterface::class, [], 30);

        if (null !== $this->slugPreparation) {
            $recipe = $recipe->cook($this->slugPreparation, SlugPreparation::class, [], 40);
        }

        if (null !== $this->objectAccessControl) {
            $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 50);
        }

        $recipe = $recipe->cook($this->saveObject, SaveObject::class, [], 60);

        $recipe = $recipe->cook($this->redirectClient, RedirectClientInterface::class, [], 70);

        $recipe = $recipe->cook($this->renderForm, RenderFormInterface::class, [], 80);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('nextStep', RenderFormInterface::class);

        if (null !== $this->defaultErrorTemplate) {
            $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);
        }

        return $recipe;
    }
}
