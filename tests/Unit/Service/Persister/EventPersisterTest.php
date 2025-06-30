<?php

namespace App\Tests\Unit\Service\Persister;

use App\Service\PayloadSanitizer;
use App\Service\Persister\EventPersister;
use PHPUnit\Framework\MockObject\MockObject;

class EventPersisterTest extends AbstractPersisterTestCase
{
    private EventPersister $persister;
    private PayloadSanitizer|MockObject $payloadSanitizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payloadSanitizer = $this->createMock(PayloadSanitizer::class);
        $this->persister = new EventPersister($this->payloadSanitizer);
        $this->persister->setConnection($this->connection);
    }

    public function testPersistFromEvents(): void
    {
        $this->payloadSanitizer->method('sanitize')->willReturn(['ref' => 'master']);

        $this->statement
            ->expects($this->once())
            ->method('executeStatement')
            ->with([
                '2489651045',
                665991,
                28688495,
                'CreateEvent',
                1,
                json_encode(['ref' => 'master']),
                '2015-01-01T15:00:00Z',
                null,
            ]);

        $count = $this->persister->persistFromEvents([$this->event]);

        $this->assertSame(1, $count);
    }
}
