<?php

namespace App\Tests\Unit\Service\Persister;

use App\Service\Persister\ActorPersister;

class ActorPersisterTest extends AbstractPersisterTestCase
{
    private ActorPersister $persister;
    protected function setUp(): void
    {
        parent::setUp();

        $this->persister = new ActorPersister();
        $this->persister->setConnection($this->connection);
    }

    public function testPersistFromEvents(): void
    {
        $this->statement
            ->expects($this->once())
            ->method('executeStatement')
            ->with([
                665991,
                'petroav',
                'https://api.github.com/users/petroav',
                'https://avatars.githubusercontent.com/u/665991?',
            ]);


        $count = $this->persister->persistFromEvents([$this->event]);

        $this->assertSame(1, $count);
    }
}
