<?php
/**
 * SlugType.php
 *
 * @since 27/11/16
 * @author gseidel
 */

namespace Enhavo\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MaskedType
 * Represents a masked input type using jquery.inputmask to restrict and complete input following mask options.
 * Find more documentation here: https://github.com/RobinHerbots/Inputmask
 *
 *
 * @package Enhavo\Bundle\FormBundle\Form\Type
 */
class MaskedType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'EnhavoAppBundle'
        ));
        $resolver->setRequired('mask_options');
        $resolver->setAllowedTypes('mask_options', 'array');
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $inputMask = $this->optionsToInputmask($options['mask_options']);
        $view->vars['attr']['data-inputmask'] = $inputMask;

    }

    private function optionsToInputmask(array $options = [])
    {
        //"'alias': 'datetime', 'placeholder': 'tt.mm.jjjj', 'inputFormat': 'dd.mm.yyyy', 'outputFormat': 'dd.mm.yyyy'"
        $settings = [];
        foreach ($options as $key => $value) {
            $settings[] = sprintf("'%s': '%s'", $key, $value);
        }

        return implode(', ', $settings);
    }

    public function getBlockPrefix()
    {
        return 'enhavo_masked';
    }

    public function getParent()
    {
        return TextType::class;
    }
}
