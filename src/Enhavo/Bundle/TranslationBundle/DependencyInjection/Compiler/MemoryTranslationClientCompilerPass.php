<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\DependencyInjection\Compiler;

use Enhavo\Bundle\TranslationBundle\Client\MemoryTranslationClient;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Puts the memory in front of the configured translation client. The client itself is
 * left untouched, so it stays usable on its own.
 */
class MemoryTranslationClientCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->getParameter('enhavo_translation.translation_client.memory.enabled')) {
            $container->removeDefinition(MemoryTranslationClient::class);

            return;
        }

        $client = $container->getParameter('enhavo_translation.translation_client.client');
        $container->getDefinition(MemoryTranslationClient::class)->setArgument(0, new Reference($client));
    }
}
