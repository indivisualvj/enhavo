<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Memory;

use Enhavo\Bundle\FormBundle\Formatter\HtmlSanitizer;

/**
 * Builds the lookup key of a memory entry.
 *
 * Source texts reach the memory from clients, commands and the admin form, all of them
 * with slightly different markup and entity encoding. Normalizing in one place is what
 * keeps a key stable, so the same text is really recognized as the same text.
 */
class TranslationMemoryNormalizer
{
    public function __construct(
        private readonly HtmlSanitizer $htmlSanitizer,
        private readonly array $htmlSanitizerConfig,
    ) {
    }

    /**
     * Entities are decoded twice on purpose: the sanitizer re-encodes what the first
     * decode resolved, so without the second pass the same text would produce two
     * different keys depending on where it came from.
     */
    public function normalize(?string $value): string
    {
        if (null === $value || '' === trim($value)) {
            return '';
        }

        $value = trim(html_entity_decode($value));
        $value = $this->htmlSanitizer->sanitize($value, $this->htmlSanitizerConfig);

        return trim(html_entity_decode($value));
    }

    public function getHash(?string $sourceLanguage, ?string $targetLanguage, ?string $sourceValue): string
    {
        return hash('xxh128', sprintf('%s:%s:%s', $sourceLanguage, $targetLanguage, $sourceValue));
    }
}
