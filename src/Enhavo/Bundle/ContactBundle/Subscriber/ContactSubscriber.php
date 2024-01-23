<?php
/**
 * @author blutze-media
 * @since 2024-01-23
 */

namespace Enhavo\Bundle\ContactBundle\Subscriber;

use DateTime;
use Enhavo\Bundle\AppBundle\Event\ResourceEvent;
use Enhavo\Bundle\AppBundle\Event\ResourceEvents;
use Enhavo\Bundle\AppBundle\Util\SecureUrlTokenGenerator;
use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SecureUrlTokenGenerator $tokenGenerator,
    )
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResourceEvents::PRE_CREATE => 'onPreCreate',
        ];
    }

    public function onPreCreate(ResourceEvent $event): void
    {
        $subject = $event->getSubject();
        if ($subject instanceof ContactInterface
            && null === $subject->getCreatedAt()
            && null === $subject->getToken()
        ) {
            $subject->setCreatedAt(new DateTime());
            $subject->setToken($this->tokenGenerator->generateToken());
        }
    }
}
