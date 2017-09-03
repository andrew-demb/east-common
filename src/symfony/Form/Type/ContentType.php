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

namespace Teknoo\East\WebsiteBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ContentType extends AbstractType
{
    use TranslatableTrait;

    /**
     * To configure this form and fields to display.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('author', EntityType::class, ['required'=>true, 'multiple'=>false]);
        $builder->add('type', EntityType::class, ['required'=>true, 'multiple'=>false]);
        $builder->add('categories', EntityType::class, ['required'=>true, 'multiple'=>true]);
        $builder->add('title', TextType::class, ['required'=>true]);
        $builder->add('subtitle', TextType::class, ['required'=>false]);
        $builder->add('slug', TextType::class, ['required'=>false]);
        $builder->add('content', TextType::class, ['required'=>false]);
        $builder->add('description', TextType::class, ['required'=>false]);

        $this->addTranslatableLocaleFieldHidden($builder);
        $this->disableNonTranslatableField($builder, $options);
    }
}