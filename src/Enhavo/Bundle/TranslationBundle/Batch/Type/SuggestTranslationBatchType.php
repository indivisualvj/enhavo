<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Batch\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Enhavo\Bundle\ApiBundle\Data\Data;
use Enhavo\Bundle\ApiBundle\Endpoint\Context;
use Enhavo\Bundle\ResourceBundle\Batch\AbstractBatchType;
use Enhavo\Bundle\TranslationBundle\Memory\TranslationSuggester;
use Enhavo\Bundle\TranslationBundle\Model\TranslationMemoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Asks the translation client for a fresh proposal for the selected memory entries.
 *
 * Same as the suggest_translation action, only for a whole selection instead of the
 * entry that is currently open.
 */
class SuggestTranslationBatchType extends AbstractBatchType
{
    private const int FLUSH_INTERVAL = 100;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslationSuggester $suggester,
    ) {
    }

    public function execute(array $options, array $ids, EntityRepository $repository, Data $data, Context $context): void
    {
        $count = 0;
        foreach ($ids as $id) {
            $entry = $repository->find($id);
            if (!$entry instanceof TranslationMemoryInterface) {
                continue;
            }

            $this->suggester->suggest($entry);

            if (0 === ++$count % self::FLUSH_INTERVAL) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'batch.suggest_translation.label',
            'confirm_message' => 'batch.suggest_translation.message.confirm',
            'translation_domain' => 'EnhavoTranslationBundle',
        ]);
    }

    public static function getName(): ?string
    {
        return 'suggest_translation';
    }
}
