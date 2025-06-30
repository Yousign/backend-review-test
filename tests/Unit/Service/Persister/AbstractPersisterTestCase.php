<?php

namespace App\Tests\Unit\Service\Persister;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractPersisterTestCase extends TestCase
{
    protected Connection|MockObject $connection;

    protected Statement|MockObject $statement;

    protected array $event;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->statement = $this->createMock(Statement::class);

        $this->connection->method('prepare')->willReturn($this->statement);

        $this->event = [
            'id' => '2489651045',
            'type' => 'CreateEvent',
            'actor' => [
                'id' => 665991,
                'login' => 'petroav',
                'gravatar_id' => '',
                'url' => 'https://api.github.com/users/petroav',
                'avatar_url' => 'https://avatars.githubusercontent.com/u/665991?'
            ],
            'repo' => [
                'id' => 28688495,
                'name' => 'petroav/6.828',
                'url' => 'https://api.github.com/repos/petroav/6.828'
            ],
            'payload' => [
                'ref' => 'master',
                'ref_type' => 'branch',
                'master_branch' => 'master',
                'description' => "Solution to homework and assignments from MIT's 6.828 (Operating Systems Engineering). Done in my spare time.",
                'pusher_type' => 'user'
            ],
            'public' => true,
            'created_at' => '2015-01-01T15:00:00Z'
        ];
    }
}
