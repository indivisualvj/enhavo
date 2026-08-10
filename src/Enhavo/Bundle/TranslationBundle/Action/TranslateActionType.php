<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Action;

use Enhavo\Bundle\ResourceBundle\Action\AbstractActionType;
use Enhavo\Bundle\ResourceBundle\Action\Type\SaveActionType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Triggers an auto translation run on the current resource.
 *
 * The option "overwrite" only selects the wording of the button and whether it asks for
 * confirmation. Whether existing translations are really replaced is decided by the
 * endpoint the route points to.
 */
class TranslateActionType extends AbstractActionType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->remove('route');
        $resolver->setRequired('route');

        $resolver->setDefaults([
            'icon' => 'translate',
            'translation_domain' => 'EnhavoTranslationBundle',
            'overwrite' => false,
            'enabled' => 'expr:resource.getId() !== null',
            'label' => fn (Options $options) => $options['overwrite'] ? 'action.label.re_translate' : 'action.label.translate',
            'confirm' => fn (Options $options) => $options['overwrite'],
            'confirm_message' => fn (Options $options) => $options['overwrite'] ? 'action.message.re_translate_confirm' : null,
            'confirm_label_ok' => fn (Options $options) => $options['overwrite'] ? 'action.label.yes' : null,
            'confirm_label_cancel' => fn (Options $options) => $options['overwrite'] ? 'action.label.no' : null,
        ]);

        $resolver->setAllowedTypes('overwrite', 'bool');
    }

    public static function getName(): ?string
    {
        return 'translate';
    }

    public static function getParentType(): ?string
    {
        return SaveActionType::class;
    }
}
