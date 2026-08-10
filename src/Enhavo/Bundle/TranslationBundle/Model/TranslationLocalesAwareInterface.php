<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Model;

/**
 * Restricts automatic translation of a resource to the returned locales.
 *
 * Resources that do not implement this interface are translated into every locale of
 * the LocaleProviderInterface.
 */
interface TranslationLocalesAwareInterface
{
    /** @return string[] */
    public function getTranslationLocales(): array;
}
