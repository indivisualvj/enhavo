<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Endpoint\Type;

use Enhavo\Bundle\ApiBundle\Data\Data;
use Enhavo\Bundle\ApiBundle\Endpoint\AbstractEndpointType;
use Enhavo\Bundle\ApiBundle\Endpoint\Context;
use Enhavo\Bundle\ResourceBundle\Authorization\Permission;
use Enhavo\Bundle\ResourceBundle\Resource\ResourceManager;
use Enhavo\Bundle\ResourceBundle\RouteResolver\RouteResolverInterface;
use Enhavo\Bundle\TranslationBundle\Model\TranslationLocalesAwareInterface;
use Enhavo\Bundle\TranslationBundle\Translation\TranslationManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TranslateResourceEndpointType extends AbstractEndpointType
{
    public function __construct(
        private readonly ResourceManager $resourceManager,
        private readonly RouteResolverInterface $routeResolver,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslationManager $translationManager,
    ) {
    }

    public function handleRequest($options, Request $request, Data $data, Context $context): void
    {
        $metadata = $this->resourceManager->getMetadata($options['resource']);
        $repository = $this->resourceManager->getRepository($options['resource']);

        $id = intval($request->query->get('id'));
        if (!$id) {
            $context->setStatusCode(404);

            return;
        }

        $resource = $repository->find($id);
        if (null === $resource) {
            $context->setStatusCode(404);

            return;
        }

        if ($options['permission']) {
            $this->denyAccessUnlessGranted(new Permission($metadata->getName(), $options['permission']), $resource);
        }

        $runtimeOptions = [
            'overwrite' => $options['overwrite'],
            'use_memory' => $options['use_memory'],
            'memory_only' => $options['memory_only'],
            'ignore_status' => $options['ignore_status'],
            'usage' => sprintf('%s:%s', $metadata->getName(), $id),
        ];

        foreach ($this->getLocales($resource) as $locale) {
            if ($this->translationManager->getDefaultLocale() === $locale) {
                continue;
            }

            // The resource stays the prompt context, the run is steered over the options.
            $this->translationManager->applyAutoTranslation($resource, $locale, null, $resource, $runtimeOptions);
        }

        $this->resourceManager->save($resource);

        $updateRoute = $options['update_route'] ?? $this->routeResolver->getRoute('update', ['api' => true]);
        $url = $this->urlGenerator->generate($updateRoute, ['id' => $id]);
        $context->setResponse(new RedirectResponse($url));
    }

    /**
     * Translating into a locale a resource is not published in is paid waste, so a
     * resource may narrow the set of locales down.
     *
     * @return string[]
     */
    private function getLocales(object $resource): array
    {
        if ($resource instanceof TranslationLocalesAwareInterface) {
            return $resource->getTranslationLocales();
        }

        return $this->translationManager->getLocales();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'resource',
        ]);

        $resolver->setDefaults([
            'permission' => null,
            'update_route' => null,
            'overwrite' => false,
            'use_memory' => true,
            'memory_only' => false,
            'ignore_status' => true,
        ]);

        $resolver->setAllowedTypes('overwrite', 'bool');
        $resolver->setAllowedTypes('use_memory', 'bool');
        $resolver->setAllowedTypes('memory_only', 'bool');
        $resolver->setAllowedTypes('ignore_status', 'bool');
    }

    public static function getName(): ?string
    {
        return 'translate_resource';
    }
}
