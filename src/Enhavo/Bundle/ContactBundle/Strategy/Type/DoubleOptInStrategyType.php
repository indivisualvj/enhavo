<?php

namespace Enhavo\Bundle\ContactBundle\Strategy\Type;

use Enhavo\Bundle\ContactBundle\Strategy\AbstractStrategyType;

class DoubleOptInStrategyType extends AbstractStrategyType
{
    public static function getName(): ?string
    {
        return 'douple-opt-in';
    }
}
