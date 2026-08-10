<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Enhavo\Bundle\TranslationBundle\Memory\TranslationMemoryNormalizer;
use Enhavo\Bundle\TranslationBundle\Model\TranslationMemoryInterface;

/**
 * Keeps the derived fields of a memory entry in sync, no matter whether it was written by
 * a translation run or by hand in the admin.
 *
 * The lookup hash is derived here, so an entry an editor created or whose source text an
 * editor changed can be found again. An entry without a translation can never carry a
 * review status.
 */
class TranslationMemoryListener
{
    /** Changes on these properties mark an entry as edited. */
    private const array TRACKED_PROPERTIES = [
        'sourceLanguage',
        'targetLanguage',
        'sourceValue',
        'targetValue',
        'status',
        'comment',
    ];

    public function __construct(
        private readonly TranslationMemoryNormalizer $normalizer,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entry = $args->getObject();
        if (!$entry instanceof TranslationMemoryInterface) {
            return;
        }

        if (null === $entry->getCreatedAt()) {
            $entry->setCreatedAt(new \DateTime());
        }

        $this->update($entry);
    }

    /**
     * Updates run on flush and not on preUpdate, because doctrine has computed the change
     * set by then and would silently drop plain setter calls.
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entry) {
            if (!$entry instanceof TranslationMemoryInterface) {
                continue;
            }

            $changes = array_keys($unitOfWork->getEntityChangeSet($entry));
            if (count(array_intersect($changes, self::TRACKED_PROPERTIES))) {
                $entry->setUpdatedAt(new \DateTime());
            }

            $this->update($entry);

            $unitOfWork->recomputeSingleEntityChangeSet($entityManager->getClassMetadata($entry::class), $entry);
        }
    }

    private function update(TranslationMemoryInterface $entry): void
    {
        $sourceValue = $this->normalizer->normalize($entry->getSourceValue());

        $entry->setSourceValue('' === $sourceValue ? null : $sourceValue);
        $entry->setSourceLength(mb_strlen($sourceValue));
        $entry->setHash($this->normalizer->getHash($entry->getSourceLanguage(), $entry->getTargetLanguage(), $sourceValue));

        if ('' === ($entry->getTargetValue() ?? '')) {
            $entry->setStatus(TranslationMemoryInterface::STATUS_OPEN);
        }
    }
}
