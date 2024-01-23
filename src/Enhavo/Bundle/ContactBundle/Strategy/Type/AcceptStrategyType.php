<?php

namespace Enhavo\Bundle\ContactBundle\Strategy\Type;

use Enhavo\Bundle\ContactBundle\Contact\ContactManager;
use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Enhavo\Bundle\ContactBundle\Strategy\AbstractStrategyType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AcceptStrategyType extends AbstractStrategyType
{
    public function __construct(
        ContactManager $contactManager
    )
    {
    }


    public function process(ContactInterface $contact, array $options): void
    {
        $this->preProcess($contact);

        $this->postProcess($contact);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

        ]);
    }

    public static function getName(): ?string
    {
        return 'accept';
    }

}
