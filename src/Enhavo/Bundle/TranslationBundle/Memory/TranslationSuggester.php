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

use Enhavo\Bundle\TranslationBundle\Client\TranslationClientInterface;
use Enhavo\Bundle\TranslationBundle\Model\TranslationMemoryInterface;

/**
 * Asks the translation client for a fresh proposal for a memory entry.
 *
 * The proposal is written into the entry only, not onto the records using it, so an
 * editor decides per entry what gets applied.
 *
 * This lives next to the manager and not inside it, because it needs the translation
 * client, which is decorated by the memory and would depend on the manager in turn.
 */
class TranslationSuggester
{
    public function __construct(
        private readonly TranslationClientInterface $translationClient,
    ) {
    }

    /**
     * @return bool whether a new proposal was written
     */
    public function suggest(TranslationMemoryInterface $entry): bool
    {
        // Reviewed wording is editorial work. The memory client protects it as well, but
        // the guard has to hold with a client that has no memory in front of it too.
        if ($entry->isReviewed()) {
            return false;
        }

        if (!$entry->getSourceValue() || !$entry->getSourceLanguage() || !$entry->getTargetLanguage()) {
            return false;
        }

        $suggestion = $this->translationClient->translate($entry->getSourceValue(), $entry->getSourceLanguage(), $entry->getTargetLanguage(), [
            'html' => $entry->isHtml(),
            'use_memory' => false,
            'ignore_status' => false,
            'overwrite' => true,
        ]);

        if (null === $suggestion || '' === $suggestion) {
            return false;
        }

        $entry->setTargetValue($suggestion);

        return true;
    }
}
