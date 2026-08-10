<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Menu;

use Enhavo\Bundle\AppBundle\Menu\AbstractMenuType;
use Enhavo\Bundle\AppBundle\Menu\Type\LinkMenuType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationMemoryMenu extends AbstractMenuType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'icon' => 'translate',
            'label' => 'translation_memory.label.translation_memory',
            'translation_domain' => 'EnhavoTranslationBundle',
            'route' => 'enhavo_translation_admin_translation_memory_index',
            'permission' => 'ROLE_ENHAVO_TRANSLATION_TRANSLATION_MEMORY_INDEX',
        ]);
    }

    public static function getName(): ?string
    {
        return 'translation_memory';
    }

    public static function getParentType(): ?string
    {
        return LinkMenuType::class;
    }
}
