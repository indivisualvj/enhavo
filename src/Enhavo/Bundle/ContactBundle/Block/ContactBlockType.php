<?php

namespace Enhavo\Bundle\ContactBundle\Block;

use Enhavo\Bundle\AppBundle\View\ViewData;
use Enhavo\Bundle\BlockBundle\Model\BlockInterface;
use Enhavo\Bundle\ContactBundle\Contact\ContactManager;
use Enhavo\Bundle\ContactBundle\Entity\ContactBlock;
use Enhavo\Bundle\ContactBundle\Factory\ContactBlockFactory;
use Enhavo\Bundle\ContactBundle\Form\Type\ContactBlockType as ContactBlockFormType;
use Enhavo\Bundle\BlockBundle\Block\AbstractBlockType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactBlockType extends AbstractBlockType
{
    public function __construct(
        private readonly ContactManager $contactManager
    )
    {

    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'model' => ContactBlock::class,
            'form' => ContactBlockFormType::class,
            'factory' => ContactBlockFactory::class,
            'template' => 'theme/block/contact-form.html.twig',
            'label' => 'contact.label.contact_form',
            'translation_domain' => 'EnhavoAContactBundle',
            'groups' => ['default', 'content']
        ]);
    }

    /**
     * @param BlockInterface|ContactBlock $block
     * @param ViewData $viewData
     * @param $resource
     * @param array $options
     * @return void
     */
    public function createViewData(BlockInterface|ContactBlock $block, ViewData $viewData, $resource, array $options): void
    {
        $viewData->set('form', $this->contactManager->createFormView($block->getKey()));
    }

    public static function getName(): ?string
    {
        return 'contact';
    }
}
