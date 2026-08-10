<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Tests\Client;

use Enhavo\Bundle\TranslationBundle\Client\MemoryTranslationClient;
use Enhavo\Bundle\TranslationBundle\Client\TranslationClientInterface;
use Enhavo\Bundle\TranslationBundle\Entity\TranslationMemory;
use Enhavo\Bundle\TranslationBundle\Memory\TranslationMemoryManager;
use Enhavo\Bundle\TranslationBundle\Model\TranslationMemoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MemoryTranslationClientTest extends TestCase
{
    private TranslationClientInterface|MockObject $innerClient;
    private TranslationMemoryManager|MockObject $memoryManager;

    protected function setUp(): void
    {
        $this->innerClient = $this->getMockBuilder(TranslationClientInterface::class)->getMock();
        $this->memoryManager = $this->getMockBuilder(TranslationMemoryManager::class)->disableOriginalConstructor()->getMock();
        $this->memoryManager->method('normalize')->willReturnCallback(fn (?string $value) => trim((string) $value));
    }

    private function createInstance(): MemoryTranslationClient
    {
        return new MemoryTranslationClient($this->innerClient, $this->memoryManager);
    }

    private function createEntry(?string $targetValue, string $status = TranslationMemoryInterface::STATUS_OPEN): TranslationMemory
    {
        $entry = new TranslationMemory();
        $entry->setSourceValue('Hello');
        $entry->setTargetValue($targetValue);
        $entry->setStatus($status);

        return $entry;
    }

    public function testMemoryHitIsServedWithoutCallingTheClient()
    {
        $this->memoryManager->method('find')->willReturn($this->createEntry('Hallo'));
        $this->innerClient->expects($this->never())->method('translate');
        $this->memoryManager->expects($this->never())->method('store');

        $this->assertEquals('Hallo', $this->createInstance()->translate('Hello', 'en', 'de'));
    }

    public function testMemoryMissIsTranslatedAndStored()
    {
        $this->memoryManager->method('find')->willReturn(null);
        $this->innerClient->expects($this->once())->method('translate')->willReturn('Hallo');
        $this->memoryManager->expects($this->once())->method('store')->willReturnCallback(function ($source, $target, $sourceValue, $targetValue) {
            $this->assertEquals('Hello', $sourceValue);
            $this->assertEquals('Hallo', $targetValue);

            return $this->createEntry('Hallo');
        });

        $this->assertEquals('Hallo', $this->createInstance()->translate('Hello', 'en', 'de'));
    }

    public function testMemoryOnlyCollectsWithoutCallingTheClient()
    {
        $this->memoryManager->method('find')->willReturn(null);
        $this->innerClient->expects($this->never())->method('translate');
        $this->memoryManager->expects($this->once())->method('store')->willReturn($this->createEntry(null));

        $this->assertNull($this->createInstance()->translate('Hello', 'en', 'de', ['memory_only' => true]));
    }

    public function testReviewedEntryIsServedEvenOnOverwrite()
    {
        $this->memoryManager->method('find')->willReturn($this->createEntry('Hallo', TranslationMemoryInterface::STATUS_REVIEWED));
        $this->innerClient->expects($this->never())->method('translate');
        $this->memoryManager->expects($this->never())->method('store');

        $result = $this->createInstance()->translate('Hello', 'en', 'de', [
            'use_memory' => false,
            'overwrite' => true,
        ]);

        $this->assertEquals('Hallo', $result);
    }

    public function testUnreviewedEntryIsTranslatedAgainOnOverwrite()
    {
        $this->memoryManager->method('find')->willReturn($this->createEntry('Alte Übersetzung'));
        $this->innerClient->expects($this->once())->method('translate')->willReturn('Neue Übersetzung');
        $this->memoryManager->expects($this->once())->method('store')->willReturn($this->createEntry('Neue Übersetzung'));

        $result = $this->createInstance()->translate('Hello', 'en', 'de', [
            'use_memory' => false,
            'overwrite' => true,
        ]);

        $this->assertEquals('Neue Übersetzung', $result);
    }

    public function testEntryWithEmptyTargetValueBlocksTranslation()
    {
        $this->memoryManager->method('find')->willReturn($this->createEntry(null));
        $this->innerClient->expects($this->never())->method('translate');

        $this->assertNull($this->createInstance()->translate('Hello', 'en', 'de'));
    }

    public function testUnreviewedEntryIsNotServedWithoutIgnoreStatus()
    {
        $this->memoryManager->method('find')->willReturn($this->createEntry('Hallo'));
        $this->innerClient->expects($this->never())->method('translate');

        $this->assertNull($this->createInstance()->translate('Hello', 'en', 'de', ['ignore_status' => false]));
    }

    public function testStoreOnlyWritesToMemoryButReturnsNull()
    {
        $this->memoryManager->method('find')->willReturn(null);
        $this->innerClient->expects($this->once())->method('translate')->willReturn('Hallo');
        $this->memoryManager->expects($this->once())->method('store')->willReturn($this->createEntry('Hallo'));

        $this->assertNull($this->createInstance()->translate('Hello', 'en', 'de', ['store_only' => true]));
    }

    public function testEmptyTextIsNotTranslated()
    {
        $this->innerClient->expects($this->never())->method('translate');
        $this->memoryManager->expects($this->never())->method('find');

        $this->assertNull($this->createInstance()->translate('   ', 'en', 'de'));
    }

    public function testContextIsHandedToTheInnerClient()
    {
        $context = new \stdClass();

        $this->memoryManager->method('find')->willReturn(null);
        $this->memoryManager->method('store')->willReturn($this->createEntry('Hallo'));
        $this->innerClient->expects($this->once())->method('translate')->willReturnCallback(function ($text, $source, $target, $options) use ($context) {
            $this->assertSame($context, $options['context']);
            $this->assertEquals(['endpoint'], $options['context_groups']);

            return 'Hallo';
        });

        $this->createInstance()->translate('Hello', 'en', 'de', [
            'context' => $context,
            'context_groups' => ['endpoint'],
        ]);
    }
}
