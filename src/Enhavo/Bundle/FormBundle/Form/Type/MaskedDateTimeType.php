<?php
/**
 * SlugType.php
 *
 * @since 27/11/16
 * @author gseidel
 */

namespace Enhavo\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MaskedDateType
 *
 * @see MaskedType
 *
 * @package Enhavo\Bundle\FormBundle\Form\Type
 */
class MaskedDateTimeType extends MaskedType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('widget', 'single_text');
        $resolver->setDefaults([
            'widget' => 'single_text',
            'allow_typing' => true,
            'mask_options' => [
                'alias' => 'datetime',
                'placeholder' => 'tt.mm.jjjj ss:mm',
                'inputFormat' => 'dd.mm.yyyy HH:MM',
                'outputFormat' => 'dd.mm.yyyy HH:MM',
            ],
        ]);
        $resolver->setRequired('mask_options');
        $resolver->setAllowedTypes('mask_options', 'array');
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
    }

    public function getBlockPrefix()
    {
        return 'enhavo_masked_date';
    }

    public function getParent()
    {
        return DateTimeType::class;
    }
}
