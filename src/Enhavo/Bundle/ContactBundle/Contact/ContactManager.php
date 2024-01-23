<?php


namespace Enhavo\Bundle\ContactBundle\Contact;


use Enhavo\Bundle\AppBundle\Resource\ResourceManager;
use Enhavo\Bundle\ContactBundle\Model\ContactInterface;

class ContactManager
{
    public function __construct(
        private readonly ResourceManager $resourceManager,
    )
    {

    }

    public function save(ContactInterface $contact): void
    {
        $this->resourceManager->save($contact);
    }
}
