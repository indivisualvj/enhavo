<?php

namespace Enhavo\Bundle\ContactBundle;

use Enhavo\Bundle\ContactBundle\Contact\Contact;
use Enhavo\Component\Type\TypeCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EnhavoContactBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new TypeCompilerPass('Contact', 'enhavo_contact.contact', Contact::class)
        );
    }
}
