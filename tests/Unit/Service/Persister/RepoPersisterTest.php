<?php

namespace App\Tests\Unit\Service\Persister;

use App\Service\Persister\RepoPersister;

class RepoPersisterTest extends AbstractPersisterTestCase
{
    private RepoPersister $persister;

    public function setUp(): void
    {
        parent::setUp();

        $this->persister = new RepoPersister();
        $this->persister->setConnection($this->connection);
    }

    public function testPersistFromEvents(): void
    {
        $this->statement
            ->expects($this->once())
            ->method('executeStatement')
            ->with([
                28688495,
                'petroav/6.828',
                'https://api.github.com/repos/petroav/6.828',
            ]);

        $persister = new RepoPersister();
        $persister->setConnection($this->connection);

        $count = $persister->persistFromEvents([$this->event]);
        $this->assertSame(1, $count);
    }
}
