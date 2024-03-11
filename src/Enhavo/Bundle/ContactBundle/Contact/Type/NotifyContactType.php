<?php
/**
 * @author blutze-media
 * @since 2024-01-24
 */

namespace Enhavo\Bundle\ContactBundle\Contact\Type;

use Enhavo\Bundle\ContactBundle\Contact\AbstractContactType;
use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotifyContactType extends AbstractContactType
{
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
        return 'notify';
    }
}
