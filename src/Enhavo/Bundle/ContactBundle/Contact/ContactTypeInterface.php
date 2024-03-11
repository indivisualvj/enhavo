<?php
/**
 * @author blutze-media
 * @since 2024-01-24
 */

namespace Enhavo\Bundle\ContactBundle\Contact;

use Enhavo\Bundle\ContactBundle\Model\ContactInterface;
use Enhavo\Component\Type\TypeInterface;

interface ContactTypeInterface extends TypeInterface
{
    public function process(ContactInterface $contact, array $options);

}
