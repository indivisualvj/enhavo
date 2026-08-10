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

use Enhavo\Bundle\TranslationBundle\Memory\TranslationMemoryManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Puts the translation memory in front of another client.
 *
 * Every source text that was translated once is stored, so the same text is never paid
 * for twice, and every record holding it receives the same wording. The decorator makes
 * the memory available to all clients without any of them knowing about it.
 *
 * Options, all of them optional:
 *
 *  - use_memory   look into the memory before calling the inner client (default true)
 *  - memory_only  never call the inner client, only serve and collect (default false)
 *  - overwrite    replace the stored translation with a fresh one (default false)
 *  - ignore_status  reuse entries of any status, not only reviewed ones (default true)
 *  - store_only   write to the memory but return null, so the caller keeps its value
 *  - usage        a label describing where the source text was found
 */
class MemoryTranslationClient implements TranslationClientInterface
{
    public function __construct(
        private readonly TranslationClientInterface $client,
        private readonly TranslationMemoryManager $memoryManager,
    ) {
    }

    public function translate(string $text, string $sourceLanguage, string $targetLanguage, array $options = []): ?string
    {
        // Only the memory options are resolved here. The untouched options are handed to
        // the inner client, which resolves the ones it knows itself.
        $memoryOptions = $this->getOptions($options);

        $text = $this->memoryManager->normalize($text);
        if ('' === $text) {
            return null;
        }

        $entry = $this->memoryManager->find($sourceLanguage, $targetLanguage, $text);

        // Reviewed wording wins over a re-translate request, so an approved entry is
        // served instead of being replaced by fresh machine output.
        $useMemory = $memoryOptions['use_memory'] || ($memoryOptions['overwrite'] && $entry?->isReviewed());

        if ($useMemory && null !== $entry) {
            $usable = ($memoryOptions['ignore_status'] || $entry->isReviewed()) && $entry->getTargetValue();
            $this->memoryManager->addUsage($entry, $memoryOptions['usage']);

            if (!$usable) {
                return null;
            }

            return $memoryOptions['store_only'] ? null : $entry->getTargetValue();
        }

        if ($memoryOptions['memory_only']) {
            // Collect the source text so it can be filled later, without spending a call.
            $this->memoryManager->store($sourceLanguage, $targetLanguage, $text, null, $memoryOptions['html'], $memoryOptions['usage']);

            return null;
        }

        $translatedText = $this->client->translate($text, $sourceLanguage, $targetLanguage, $options);
        if (null === $translatedText) {
            return null;
        }

        $translatedText = $this->memoryManager->normalize($translatedText);

        if (null === $entry || $memoryOptions['overwrite']) {
            $this->memoryManager->store($sourceLanguage, $targetLanguage, $text, $translatedText, $memoryOptions['html'], $memoryOptions['usage']);
        }

        return $memoryOptions['store_only'] ? null : $translatedText;
    }

    protected function getOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setIgnoreUndefined();
        $resolver->setDefaults([
            'html' => false,
            'use_memory' => true,
            'memory_only' => false,
            'overwrite' => false,
            'ignore_status' => true,
            'store_only' => false,
            'usage' => null,
        ]);
        $resolver->setAllowedTypes('html', 'bool');
        $resolver->setAllowedTypes('use_memory', 'bool');
        $resolver->setAllowedTypes('memory_only', 'bool');
        $resolver->setAllowedTypes('overwrite', 'bool');
        $resolver->setAllowedTypes('ignore_status', 'bool');
        $resolver->setAllowedTypes('store_only', 'bool');
        $resolver->setAllowedTypes('usage', ['null', 'string']);

        return $resolver->resolve($options);
    }
}
