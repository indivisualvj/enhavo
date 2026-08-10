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

use Enhavo\Bundle\MediaBundle\Factory\FileFactory;

class ConfigContextProvider implements ContextProviderInterface
{
    /**
     * @param array<string, array{text: string|null, files: string[]}> $locales
     */
    public function __construct(
        private readonly ?string $text,
        private readonly array $files,
        private readonly array $locales,
        private readonly string $projectDir,
        private readonly FileFactory $fileFactory,
    ) {
    }

    public function getText(?string $locale = null): ?string
    {
        $texts = array_filter([$this->text, $this->locales[$locale]['text'] ?? null]);

        if (0 === count($texts)) {
            return null;
        }

        return implode(PHP_EOL.PHP_EOL, $texts);
    }

    public function getFiles(?string $locale = null): array
    {
        $paths = array_merge($this->files, $this->locales[$locale]['files'] ?? []);

        $files = [];
        foreach ($paths as $path) {
            $files[] = $this->fileFactory->createFromPath($this->projectDir.'/'.$path);
        }

        return $files;
    }
}
