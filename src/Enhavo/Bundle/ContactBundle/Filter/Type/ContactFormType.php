<?php
namespace Enhavo\Bundle\ContactBundle\Filter\Type;

use Enhavo\Bundle\AppBundle\Filter\Type\OptionType;
use Enhavo\Bundle\ContactBundle\Contact\ContactManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactFormType extends OptionType
{
    public function __construct(
        TranslatorInterface $translator,
        private readonly ContactManager      $contactManager,
    )
    {
        parent::__construct($translator);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'component' => 'filter-option',
            'options' => $this->contactManager->getChoices(),
        ]);
    }

    public function getType(): string
    {
        return 'contact_form';
    }
}
