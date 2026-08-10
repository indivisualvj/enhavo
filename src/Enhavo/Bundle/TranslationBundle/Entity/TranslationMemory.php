<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Entity;

use Enhavo\Bundle\AppBundle\Model\TimestampableTrait;
use Enhavo\Bundle\TranslationBundle\Model\TranslationMemoryInterface;

class TranslationMemory implements TranslationMemoryInterface
{
    use TimestampableTrait;

    private ?int $id = null;
    private ?string $hash = null;
    private ?string $sourceLanguage = null;
    private ?string $targetLanguage = null;
    private ?string $sourceValue = null;
    private ?string $targetValue = null;
    private bool $html = false;
    private ?string $status = self::STATUS_OPEN;
    private ?string $comment = null;
    private ?int $sourceLength = null;
    private ?string $usages = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): void
    {
        $this->hash = $hash;
    }

    public function getSourceLanguage(): ?string
    {
        return $this->sourceLanguage;
    }

    public function setSourceLanguage(?string $sourceLanguage): void
    {
        $this->sourceLanguage = $sourceLanguage;
    }

    public function getTargetLanguage(): ?string
    {
        return $this->targetLanguage;
    }

    public function setTargetLanguage(?string $targetLanguage): void
    {
        $this->targetLanguage = $targetLanguage;
    }

    public function getSourceValue(): ?string
    {
        return $this->sourceValue;
    }

    public function setSourceValue(?string $sourceValue): void
    {
        $this->sourceValue = $sourceValue;
    }

    public function getTargetValue(): ?string
    {
        return $this->targetValue;
    }

    public function setTargetValue(?string $targetValue): void
    {
        $this->targetValue = $targetValue;
    }

    public function isHtml(): bool
    {
        return $this->html;
    }

    public function setHtml(bool $html): void
    {
        $this->html = $html;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function isReviewed(): bool
    {
        return self::STATUS_REVIEWED === $this->status;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getSourceLength(): ?int
    {
        return $this->sourceLength;
    }

    public function setSourceLength(?int $sourceLength): void
    {
        $this->sourceLength = $sourceLength;
    }

    public function getUsages(): ?string
    {
        return $this->usages;
    }

    public function setUsages(?string $usages): void
    {
        $this->usages = $usages;
    }

    public function getUsageList(): array
    {
        if (null === $this->usages || '' === $this->usages) {
            return [];
        }

        return explode(PHP_EOL, $this->usages);
    }

    public function setUsageList(array $usages): void
    {
        $this->usages = 0 === count($usages) ? null : implode(PHP_EOL, $usages);
    }

    public function addUsage(string $usage): void
    {
        $usages = $this->getUsageList();
        if (in_array($usage, $usages, true)) {
            return;
        }

        $usages[] = $usage;
        sort($usages);
        $this->setUsageList($usages);
    }

    public function getUsageCount(): int
    {
        return count($this->getUsageList());
    }
}
