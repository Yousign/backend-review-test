<?php

namespace App\Tests\Entity;

use App\Factory\RepoFactory;
use PHPUnit\Framework\TestCase;

class RepoTest extends TestCase
{
    public function testRepoFromArray(): void
    {
        $data = [
            'id' => "456",
            'name' => 'ben/repo',
            'url' => 'https://github.com/ben/repo',
        ];

        $repo = RepoFactory::fromArray($data);

        $this->assertSame(456, $repo->id());
        $this->assertSame('ben/repo', $repo->name());
        $this->assertSame('https://github.com/ben/repo', $repo->url());
    }
}
