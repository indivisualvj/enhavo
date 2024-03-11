<?php
/**
 * @author blutze-media
 * @since 2024-01-24
 */

namespace Enhavo\Bundle\ContactBundle\Contact;

use Enhavo\Bundle\ContactBundle\Contact\Type\ContactType;
use Enhavo\Bundle\ContactBundle\Event\ContactEvent;
use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Enhavo\Component\Type\AbstractType;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractContactType extends AbstractType implements ContactTypeInterface
{
    /** @var ContactTypeInterface */
    protected $parent;

    public function __construct(
        private readonly EventDispatcher $eventDispatcher,
    )
    {

    }

    public function process(ContactInterface $contact, array $options)
    {
        return $this->parent->process($contact, $options);
    }

    protected function preProcess(ContactInterface $contact): void
    {
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(new ContactEvent(ContactEvent::PRE_PROCESS, $contact), ContactEvent::PRE_PROCESS);
        } else {
            $this->parent->preProcess($contact);
        }
    }

    protected function postProcess(ContactInterface $contact): void
    {
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(new ContactEvent(ContactEvent::POST_PROCESS, $contact), ContactEvent::POST_PROCESS);
        } else {
            $this->parent->postProcess($contact);
        }
    }

    public static function getParentType(): ?string
    {
        return ContactType::class;
    }

}
