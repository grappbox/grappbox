<?php

/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 * COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\ShowcaseBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ButtonTypeIconExtension extends AbstractTypeExtension
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('icon', $options['icon']);
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['icon'] = $options['icon'];
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'icon' => null,
        ));
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */

    public function getExtendedType()
    {
        return 'button';
    }

}
