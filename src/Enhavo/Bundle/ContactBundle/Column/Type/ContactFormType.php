<?php

namespace Enhavo\Bundle\ContactBundle\Column\Type;

use Enhavo\Bundle\AppBundle\Column\AbstractColumnType;
use Enhavo\Bundle\AppBundle\Exception\PropertyNotExistsException;
use Enhavo\Bundle\ContactBundle\Contact\ContactManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractColumnType
{
    private array $types;

    public function __construct(
        private readonly ContactManager $contactManager,
    ) {
        $this->types = $this->contactManager->getChoices();
    }

    /**
     * @throws PropertyNotExistsException
     */
    public function createResourceViewData(array $options, $resource)
    {
        $value = $this->getProperty($resource, $options['property']);

        return $this->types[$value] ?: $value;
    }

    public function createColumnViewData(array $options): array
    {
        $data = parent::createColumnViewData($options);

        return array_merge($data, [
            'property' => $options['property'],
            'sortingProperty' => ($options['sortingProperty'] ?: $options['property'])
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'component' => 'column-text',
            'sortingProperty' => null,
        ]);
        $resolver->setRequired(['property']);
    }

    public function getType(): string
    {
        return 'contact_form';
    }
}
