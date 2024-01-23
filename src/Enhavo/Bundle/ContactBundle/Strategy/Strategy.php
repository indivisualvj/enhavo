<?php

namespace Enhavo\Bundle\ContactBundle\Strategy;

use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Enhavo\Component\Type\AbstractContainerType;

class Strategy extends AbstractContainerType
{
    /** @var StrategyTypeInterface */
    protected $type;

    public function process(ContactInterface $contact)
    {
        return $this->type->process($contact, $this->options);
    }
}
