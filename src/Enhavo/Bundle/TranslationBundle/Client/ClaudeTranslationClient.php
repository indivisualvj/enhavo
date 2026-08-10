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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClaudeTranslationClient implements TranslationClientInterface
{
    /**
     * Answer of the model for input it can not translate, e.g. article numbers or plain
     * number columns. Without it the model replies with a question or an explanation,
     * which would then be stored as a translation.
     */
    private const string INVALID_REQUEST = '--invalid-request--';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ContextNormalizer $contextNormalizer,
        private readonly ContextProviderInterface $contextProvider,
        private readonly ?string $apiKey,
        private readonly ?string $version = null,
        private readonly ?string $model = null,
        private readonly int $timeout = 600,
        private readonly int $maxTokens = 4096,
    ) {
    }

    public function translate(string $text, string $sourceLanguage, string $targetLanguage, array $options = []): ?string
    {
        $options = $this->getOptions($options);

        $response = $this->client->request('POST', 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->version ?? '2023-06-01',
            ],
            'json' => [
                'model' => $this->model ?? 'claude-haiku-4-5-20251001',
                'max_tokens' => $this->maxTokens,
                'system' => $this->getSystemPrompt($sourceLanguage, $targetLanguage, $options),
                'messages' => [
                    [
                        'role' => 'user',
                        // Naming the direction again measurably improves short and
                        // ambiguous strings, where the system prompt alone is not enough.
                        'content' => sprintf('Translate from %s to %s: %s', $sourceLanguage, $targetLanguage, $text),
                    ],
                ],
            ],
            'timeout' => $this->timeout,
        ]);

        $data = $response->toArray();
        $translatedText = $data['content'][0]['text'] ?? null;

        if (null === $translatedText || str_starts_with($translatedText, self::INVALID_REQUEST)) {
            return null;
        }

        return $translatedText;
    }

    /**
     * Every block but the first is marked as ephemeral, so the same context is billed
     * once and reused over a whole translation run.
     */
    private function getSystemPrompt(string $sourceLanguage, string $targetLanguage, array $options): array
    {
        $contextText = $this->contextProvider->getText($targetLanguage);
        $contextFiles = $this->contextProvider->getFiles($targetLanguage);

        $mainPrompt = sprintf(
            'You are a professional translator. Translate the given text from %s to %s. Return only the translated text without any explanation or additional content.',
            $sourceLanguage,
            $targetLanguage,
        );

        if ($options['html']) {
            $mainPrompt .= ' The text contains HTML markup. Preserve all HTML tags exactly as they are and only translate the text content.';
        }

        if ($options['context'] || null !== $contextText || count($contextFiles) > 0) {
            $mainPrompt .= ' Use the context for terminology and tone. Output only the translation.';
        }

        $mainPrompt .= sprintf(' Do not ask follow-up questions. In cases you cannot translate, reply with "%s".', self::INVALID_REQUEST);

        $systemPrompt = [[
            'type' => 'text',
            'text' => $mainPrompt,
        ]];

        if ($options['context']) {
            $contextParts = array_filter([$this->contextNormalizer->getText($options['context'], $options['context_groups'])]);
            // Without the guard an empty normalizer result produces a "Context: ." block
            // that says nothing and occupies one of the cache breakpoints.
            if (count($contextParts) > 0) {
                $systemPrompt[] = [
                    'type' => 'text',
                    'text' => sprintf('Context: %s.', implode('. ', $contextParts)),
                    'cache_control' => ['type' => 'ephemeral'],
                ];
            }
        }

        if (null !== $contextText) {
            $systemPrompt[] = [
                'type' => 'text',
                'text' => $contextText,
                'cache_control' => ['type' => 'ephemeral'],
            ];
        }

        foreach ($contextFiles as $file) {
            $systemPrompt[] = [
                'type' => 'text',
                'text' => sprintf('A document "%s" with content: %s', $file->getBasename(), $file->getContent()->getContent()),
                'cache_control' => ['type' => 'ephemeral'],
            ];
        }

        return $systemPrompt;
    }

    protected function getOptions($options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setIgnoreUndefined();
        $resolver->setDefaults([
            'html' => false,
            'context' => null,
            'context_groups' => [],
        ]);

        return $resolver->resolve($options);
    }
}
