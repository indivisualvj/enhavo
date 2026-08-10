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
use Enhavo\Bundle\ResourceBundle\Form\FormErrorNormalizer;
use Enhavo\Bundle\ResourceBundle\Input\Input;
use Enhavo\Bundle\ResourceBundle\Input\InputFactory;
use Enhavo\Bundle\ResourceBundle\Resource\ResourceManager;
use Enhavo\Bundle\ResourceBundle\RouteResolver\RouteResolverInterface;
use Enhavo\Bundle\TranslationBundle\Memory\TranslationMemoryManager;
use Enhavo\Bundle\TranslationBundle\Model\TranslationMemoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Applies the target value of a single memory entry to every translation that holds the
 * same source text. The submitted form is saved first, so a value corrected in the form
 * is the one that gets pushed.
 */
class PushTranslationEndpointType extends AbstractEndpointType
{
    public function __construct(
        private readonly InputFactory $inputFactory,
        private readonly ResourceManager $resourceManager,
        private readonly RouteResolverInterface $routeResolver,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly FormErrorNormalizer $formErrorNormalizer,
        private readonly TranslationMemoryManager $memoryManager,
    ) {
    }

    public function handleRequest($options, Request $request, Data $data, Context $context): void
    {
        /** @var Input $input */
        $input = $this->inputFactory->create($options['input']);

        $entry = $input->getResource();
        if (!$entry instanceof TranslationMemoryInterface) {
            $context->setStatusCode(404);

            return;
        }

        $this->denyAccessUnlessGranted(new Permission($input->getResourceName(), $options['permission']), $entry);

        $form = $input->createForm($entry);
        if ($form) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && !$form->isValid()) {
                $data->set('errors', $this->formErrorNormalizer->normalize($form));
                $context->setStatusCode(400);

                return;
            }
        }

        $this->memoryManager->push($entry);
        $this->resourceManager->save($entry);

        $updateRoute = $this->routeResolver->getRoute('update', ['api' => true]);
        $url = $this->urlGenerator->generate($updateRoute, ['id' => $entry->getId()]);
        $context->setResponse(new RedirectResponse($url));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'input',
        ]);

        $resolver->setDefaults([
            'permission' => Permission::UPDATE,
        ]);
    }

    public static function getName(): ?string
    {
        return 'push_translation';
    }
}
