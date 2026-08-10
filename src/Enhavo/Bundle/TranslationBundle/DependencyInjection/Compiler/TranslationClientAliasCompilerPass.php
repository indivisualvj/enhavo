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
use Enhavo\Bundle\TranslationBundle\Client\TranslationClientInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TranslationClientAliasCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // With the memory enabled the decorator becomes the entry point, it wraps the
        // configured client and hands everything it can not answer over to it.
        if ($container->getParameter('enhavo_translation.translation_client.memory.enabled')) {
            $container->setAlias(TranslationClientInterface::class, new Alias(MemoryTranslationClient::class));

            return;
        }

        $service = $container->getParameter('enhavo_translation.translation_client.client');
        $container->setAlias(TranslationClientInterface::class, new Alias($service));
    }
}
