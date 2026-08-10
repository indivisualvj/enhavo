<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Model;

/**
 * A single source text and its translation into one target language.
 *
 * Entries are shared over the whole application, so every record holding the same
 * source text receives the same translation. Only entries with status "reviewed" are
 * protected against being rewritten by an automatic translation run.
 */
interface TranslationMemoryInterface
{
    public const string STATUS_OPEN = 'open';
    public const string STATUS_REVIEWED = 'reviewed';
    public const string STATUS_FEEDBACK = 'feedback';

    public const array STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_REVIEWED,
        self::STATUS_FEEDBACK,
    ];

    public function getId(): ?int;

    public function getHash(): ?string;

    public function setHash(?string $hash): void;

    public function getSourceLanguage(): ?string;

    public function setSourceLanguage(?string $sourceLanguage): void;

    public function getTargetLanguage(): ?string;

    public function setTargetLanguage(?string $targetLanguage): void;

    public function getSourceValue(): ?string;

    public function setSourceValue(?string $sourceValue): void;

    public function getTargetValue(): ?string;

    public function setTargetValue(?string $targetValue): void;

    public function isHtml(): bool;

    public function setHtml(bool $html): void;

    public function getStatus(): ?string;

    public function setStatus(?string $status): void;

    public function isReviewed(): bool;

    public function getComment(): ?string;

    public function setComment(?string $comment): void;

    public function getSourceLength(): ?int;

    public function setSourceLength(?int $sourceLength): void;

    public function getUsages(): ?string;

    public function setUsages(?string $usages): void;

    /** @return string[] */
    public function getUsageList(): array;

    /** @param string[] $usages */
    public function setUsageList(array $usages): void;

    public function addUsage(string $usage): void;

    public function getUsageCount(): int;
}
