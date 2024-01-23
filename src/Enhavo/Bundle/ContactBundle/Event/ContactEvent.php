<?php

namespace Enhavo\Bundle\ContactBundle\Event;

use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ContactEvent extends Event
{
    const PRE_PROCESS = 'pre-process';
    const POST_PROCESS = 'post-process';

    public function __construct(
        private readonly string $type,
        private readonly ContactInterface $contact
    )
    {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContact(): ContactInterface
    {
        return $this->contact;
    }


}
