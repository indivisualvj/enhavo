<?php

namespace Enhavo\Bundle\ContactBundle\Strategy\Type;

use Enhavo\Bundle\ContactBundle\Event\ContactEvent;
use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Enhavo\Bundle\ContactBundle\Strategy\StrategyTypeInterface;
use Enhavo\Component\Type\AbstractType;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrategyType extends AbstractType implements StrategyTypeInterface
{
    public function __construct(
        private readonly EventDispatcher $eventDispatcher,
    )
    {

    }

    public function process(ContactInterface $contact, array $options)
    {
        return null;
    }

    protected function preProcess(ContactInterface $contact): void
    {
        $this->eventDispatcher->dispatch(new ContactEvent(ContactEvent::PRE_PROCESS, $contact), ContactEvent::PRE_PROCESS);
    }

    protected function postProcess(ContactInterface $contact): void
    {
        $this->eventDispatcher->dispatch(new ContactEvent(ContactEvent::POST_PROCESS, $contact), ContactEvent::POST_PROCESS);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'success_message' => null,
            'translation_domain' => 'EnhavoContactBundle',
        ]);
        $resolver->setRequired('success_message');
        $resolver->setAllowedTypes('success_message', []);
    }
}
