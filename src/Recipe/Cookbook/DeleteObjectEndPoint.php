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
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\DeleteObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\DeleteObject;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * HTTP EndPoint Recipe able to delete a persisted object implementing the class
 * `Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface`.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class DeleteObjectEndPoint implements DeleteObjectEndPointInterface
{
    use BaseCookbookTrait;

    /**
     * @param array<string, string> $loadObjectWiths
     */
    public function __construct(
        RecipeInterface $recipe,
        private LoadObject $loadObject,
        private DeleteObject $deleteObject,
        private RedirectClientInterface $redirectClient,
        private RenderError $renderError,
        private ?ObjectAccessControlInterface $objectAccessControl = null,
        private ?string $defaultErrorTemplate = null,
        private array $loadObjectWiths = [],
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient(LoaderInterface::class, 'loader'));
        $recipe = $recipe->require(new Ingredient('string', 'id'));
        $recipe = $recipe->require(new Ingredient('string', 'route'));

        $recipe = $recipe->cook($this->loadObject, LoadObject::class, $this->loadObjectWiths, 10);

        if (null !== $this->objectAccessControl) {
            $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 20);
        }

        $recipe = $recipe->cook($this->deleteObject, DeleteObject::class, [], 30);

        $recipe = $recipe->cook($this->redirectClient, RedirectClientInterface::class, [], 40);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        if (null !== $this->defaultErrorTemplate) {
            $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);
        }

        return $recipe;
    }
}
