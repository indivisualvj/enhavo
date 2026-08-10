<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Client;

use Enhavo\Bundle\MediaBundle\Model\FileInterface;

/**
 * Provides the terminology and style context a translation client puts into its prompt.
 *
 * The locale is the target locale of the translation. Implementations may return
 * guidelines that only apply to that locale, next to the ones that apply to all of them.
 */
interface ContextProviderInterface
{
    public function getText(?string $locale = null): ?string;

    /** @return FileInterface[] */
    public function getFiles(?string $locale = null): array;
}
