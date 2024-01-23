<?php

namespace Enhavo\Bundle\ContactBundle\Strategy;

use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Enhavo\Component\Type\TypeInterface;

interface StrategyTypeInterface extends TypeInterface
{
    public function process(ContactInterface $contact, array $options);

}
