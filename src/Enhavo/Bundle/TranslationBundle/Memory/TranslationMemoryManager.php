<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Memory;

use Doctrine\ORM\EntityManagerInterface;
use Enhavo\Bundle\ResourceBundle\Factory\FactoryInterface;
use Enhavo\Bundle\ResourceBundle\Resource\ResourceManager;
use Enhavo\Bundle\TranslationBundle\Entity\Translation;
use Enhavo\Bundle\TranslationBundle\Model\TranslationMemoryInterface;
use Enhavo\Bundle\TranslationBundle\Repository\TranslationMemoryRepository;

/**
 * Lookup, storage and distribution of translation memory entries.
 *
 * Source and target text are normalized here and nowhere else, so that a lookup key
 * stays stable no matter which client or command produced the text. Entries created
 * within one request are kept in a runtime cache, because they are only written on
 * flush and would otherwise be created twice.
 */
class TranslationMemoryManager
{
    /** @var array<string, TranslationMemoryInterface> */
    private array $runtimeMemory = [];

    public function __construct(
        private readonly TranslationMemoryRepository $repository,
        private readonly FactoryInterface $factory,
        private readonly ResourceManager $resourceManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslationMemoryNormalizer $normalizer,
    ) {
    }

    public function normalize(?string $value): string
    {
        return $this->normalizer->normalize($value);
    }

    public function find(string $sourceLanguage, string $targetLanguage, string $sourceValue): ?TranslationMemoryInterface
    {
        $hash = $this->normalizer->getHash($sourceLanguage, $targetLanguage, $sourceValue);

        if (isset($this->runtimeMemory[$hash])) {
            return $this->runtimeMemory[$hash];
        }

        $entry = $this->repository->findOneByHash($hash);
        if (null !== $entry) {
            $this->runtimeMemory[$hash] = $entry;
        }

        return $entry;
    }

    /**
     * Writes a source text and its translation into the memory. An existing entry is
     * updated, unless it is reviewed — reviewed wording is editorial work and is never
     * replaced by machine output. The entry is persisted but not flushed.
     */
    public function store(
        string $sourceLanguage,
        string $targetLanguage,
        string $sourceValue,
        ?string $targetValue = null,
        bool $html = false,
        ?string $usage = null,
    ): TranslationMemoryInterface {
        $entry = $this->find($sourceLanguage, $targetLanguage, $sourceValue);

        if (null !== $entry && $entry->isReviewed()) {
            $this->addUsage($entry, $usage);

            return $entry;
        }

        if (null === $entry) {
            /** @var TranslationMemoryInterface $entry */
            $entry = $this->factory->createNew();
            $entry->setSourceLanguage($sourceLanguage);
            $entry->setTargetLanguage($targetLanguage);
            $entry->setSourceValue($sourceValue);
            // The hash is derived by the listener as well, it is set here so the entry is
            // already found in the runtime memory before the first flush.
            $entry->setHash($this->normalizer->getHash($sourceLanguage, $targetLanguage, $sourceValue));
            $this->entityManager->persist($entry);
            $this->runtimeMemory[$entry->getHash()] = $entry;
        }

        $entry->setTargetValue($targetValue);
        $entry->setHtml($html);
        $entry->setStatus(TranslationMemoryInterface::STATUS_OPEN);
        $this->addUsage($entry, $usage);

        return $entry;
    }

    public function addUsage(TranslationMemoryInterface $entry, ?string $usage): void
    {
        if (null === $usage || '' === $usage) {
            return;
        }

        $entry->addUsage($usage);
    }

    /**
     * Writes the target value of an entry into every translation holding the same source
     * text and rebuilds its usage list. Nothing is flushed here.
     *
     * Matching records are looked up per referenced class and property, so only
     * translations that really match are hydrated. Loading all translations of a locale
     * would resolve thousands of entities one by one through the reference listener.
     *
     * @return int number of translations that were written
     */
    public function push(TranslationMemoryInterface $entry): int
    {
        $locale = $entry->getTargetLanguage();
        $sourceValue = $entry->getSourceValue();
        $entry->setUsages(null);

        if (null === $locale || null === $sourceValue) {
            return 0;
        }

        $count = 0;
        foreach ($this->getReferences($locale) as $reference) {
            $refIds = $this->findMatchingIds($reference['class'], $reference['property'], $sourceValue);
            if (0 === count($refIds)) {
                continue;
            }

            /** @var Translation[] $translations */
            $translations = $this->entityManager->getRepository(Translation::class)->findBy([
                'locale' => $locale,
                'class' => $reference['class'],
                'property' => $reference['property'],
                'refId' => $refIds,
            ]);

            foreach ($translations as $translation) {
                $translation->setTranslation($entry->getTargetValue());
                $entry->addUsage(sprintf('%s:%s[%s]', $translation->getClass(), $translation->getRefId(), $translation->getProperty()));
                ++$count;
            }
        }

        return $count;
    }

    /**
     * All class and property combinations that are translated in the given locale.
     * Entries without class can not be resolved to a record and are left out.
     *
     * @return array<int, array{class: string, property: string}>
     */
    private function getReferences(string $locale): array
    {
        return $this->entityManager->createQuery(sprintf(
            'SELECT t.class AS class, t.property AS property FROM %s t WHERE t.locale = :locale AND t.class IS NOT NULL AND t.class != :empty GROUP BY t.class, t.property',
            Translation::class
        ))
            ->setParameter('locale', $locale)
            ->setParameter('empty', '')
            ->getArrayResult()
        ;
    }

    /**
     * Ids of all records of the referenced class whose property holds the source text.
     * Only the id and the property itself are read, so neither the records nor their
     * translations are loaded here.
     *
     * @return int[]
     */
    private function findMatchingIds(string $class, string $property, string $sourceValue): array
    {
        $modelClass = $this->resolveClass($class);
        if (null === $modelClass) {
            return [];
        }

        try {
            $classMetadata = $this->entityManager->getClassMetadata($modelClass);
        } catch (\Throwable) {
            return [];
        }

        if (!$classMetadata->hasField($property)) {
            return [];
        }

        $rows = $this->entityManager->createQuery(sprintf(
            'SELECT o.%s AS id, o.%s AS value FROM %s o WHERE o.%s IS NOT NULL',
            $classMetadata->getSingleIdentifierFieldName(), $property, $modelClass, $property
        ))->getArrayResult();

        $sourceText = $this->plainText($sourceValue);
        $ids = [];

        foreach ($rows as $row) {
            $value = (string) $row['value'];

            if (trim(html_entity_decode($value)) === $sourceValue) {
                $ids[] = $row['id'];
                continue;
            }

            // Normalizing is expensive, so it only runs for values that could still
            // match, meaning their text content is equal and only the markup differs.
            if ($this->plainText($value) !== $sourceText) {
                continue;
            }

            if ($this->normalize($value) === $sourceValue) {
                $ids[] = $row['id'];
            }
        }

        return $ids;
    }

    private function resolveClass(string $class): ?string
    {
        if (class_exists($class)) {
            return $class;
        }

        return $this->resourceManager->getMetadata($class)?->getModelClass();
    }

    private function plainText(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim(strip_tags(html_entity_decode($value))));
    }
}
