<?php

namespace Enhavo\Bundle\ContactBundle\Strategy\Type;

use Enhavo\Bundle\ContactBundle\Strategy\AbstractStrategyType;

class NotifyStrategyType extends AbstractStrategyType
{

    public static function getName(): ?string
    {
        return 'notify';
    }
}
