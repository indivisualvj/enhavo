<?php


namespace Enhavo\Bundle\ContactBundle\Form\Type;


use Enhavo\Bundle\ContactBundle\Contact\ContactManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormChoiceType extends AbstractType
{

    public function __construct(
        private readonly ContactManager $contactManager,
    )
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => array_flip($this->contactManager->getChoices()),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
