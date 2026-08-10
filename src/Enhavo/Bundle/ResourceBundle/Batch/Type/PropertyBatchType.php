<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\ResourceBundle\Batch\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Enhavo\Bundle\ApiBundle\Data\Data;
use Enhavo\Bundle\ApiBundle\Endpoint\Context;
use Enhavo\Bundle\ResourceBundle\Batch\AbstractBatchType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Sets one property of every selected resource to a fixed value, e.g. to move records
 * through a simple status field without writing a batch type for each value.
 */
class PropertyBatchType extends AbstractBatchType
{
    private const int FLUSH_INTERVAL = 100;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(array $options, array $ids, EntityRepository $repository, Data $data, Context $context): void
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $count = 0;
        foreach ($ids as $id) {
            $resource = $repository->find($id);
            if (null === $resource) {
                continue;
            }

            $propertyAccessor->setValue($resource, $options['property'], $options['value']);

            if (0 === ++$count % self::FLUSH_INTERVAL) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'property',
            'value',
        ]);

        $resolver->setDefaults([
            'confirm_message' => 'batch.property.message.confirm',
            'translation_domain' => 'EnhavoResourceBundle',
        ]);
    }

    public static function getName(): ?string
    {
        return 'property';
    }
}
