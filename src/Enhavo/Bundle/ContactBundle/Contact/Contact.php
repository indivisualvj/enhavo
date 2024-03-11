<?php
/**
 * @author blutze-media
 * @since 2024-01-24
 */

namespace Enhavo\Bundle\ContactBundle\Contact;

use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Enhavo\Component\Type\AbstractContainerType;

class Contact extends AbstractContainerType
{
    /** @var ContactTypeInterface */
    protected $type;

    public function process(ContactInterface $contact)
    {
        return $this->type->process($contact, $this->options);
    }
}
