<?php

namespace App\Tests\Entity;

use App\Factory\ActorFactory;
use PHPUnit\Framework\TestCase;

class ActorTest extends TestCase
{
    public function testActorFromArray(): void
    {
        $data = [
            'id' => "123",
            'login' => 'ben-dev',
            'url' => 'https://github.com/ben-dev',
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123?v=4',
        ];

        $actor = ActorFactory::fromArray($data);

        $this->assertSame(123, $actor->id());
        $this->assertSame('ben-dev', $actor->login());
        $this->assertSame('https://github.com/ben-dev', $actor->url());
        $this->assertSame('https://avatars.githubusercontent.com/u/123?v=4', $actor->avatarUrl());
    }
}
