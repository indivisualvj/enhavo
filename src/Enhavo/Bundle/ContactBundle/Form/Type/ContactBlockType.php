<?php

namespace Enhavo\Bundle\ContactBundle\Form\Type;


use Enhavo\Bundle\ContactBundle\Entity\ContactBlock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('key', ContactFormChoiceType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => ContactBlock::class
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'enhavo_contact_contact_block';
    }
}
