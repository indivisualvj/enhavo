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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Saves the current memory entry and replaces its translation with a fresh proposal of
 * the translation client.
 */
class SuggestTranslationActionType extends AbstractActionType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->remove('route');
        $resolver->setRequired('route');

        $resolver->setDefaults([
            'icon' => 'replay',
            'label' => 'action.label.suggest_translation',
            'translation_domain' => 'EnhavoTranslationBundle',
            'enabled' => 'expr:resource.getId() !== null',
            'confirm' => true,
            'confirm_message' => 'action.message.suggest_translation_confirm',
            'confirm_label_ok' => 'action.label.yes',
            'confirm_label_cancel' => 'action.label.no',
        ]);
    }

    public static function getName(): ?string
    {
        return 'suggest_translation';
    }

    public static function getParentType(): ?string
    {
        return SaveActionType::class;
    }
}
