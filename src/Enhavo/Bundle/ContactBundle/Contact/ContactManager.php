<?php

namespace Enhavo\Bundle\ContactBundle\Contact;

use Enhavo\Bundle\ApiBundle\Normalizer\DataNormalizer;
use Enhavo\Bundle\AppBundle\Resource\ResourceManager;
use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Enhavo\Component\Type\FactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;

class ContactManager
{
    public function __construct(
        private readonly FactoryInterface $strategyFactory,
        private readonly ResourceManager $resourceManager,
        private readonly DataNormalizer $dataNormalizer,
        private readonly FormFactoryInterface $formFactory,
        private readonly RouterInterface $router,
    )
    {

    }

    public function save(ContactInterface $contact): void
    {
        $this->resourceManager->save($contact);
    }

    public function getChoices(): array
    {
        return [];
    }

    public function getType(string $key): void
    {

    }

    public function createFormViewData(string $key): array
    {

    }
}
