<?php

namespace App\Tests\Service;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\Service\GitHubEventImporter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GitHubEventImporterTest extends TestCase
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testImportSingleValidEvent(): void
    {
        // 1. Prépare un faux event JSON
        $event = [
            'id' => 123,
            'type' => 'PushEvent',
            'actor' => [
                'id' => 1,
                'login' => 'octocat',
                'url' => 'https://github.com/octocat',
                'avatar_url' => 'https://avatars.githubusercontent.com/u/1',
            ],
            'repo' => [
                'id' => 99,
                'name' => 'octo/repo',
                'url' => 'https://github.com/octo/repo',
            ],
            'payload' => ['size' => 1],
            'created_at' => '2025-06-10T12:00:00Z',
        ];

        $gzContent = gzencode(json_encode($event) . "\n");

        // 2. Mock HTTP Response
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getContent')->willReturn($gzContent);

        $http = $this->createMock(HttpClientInterface::class);
        $http->method('request')->willReturn($response);

        $logger = $this->createMock(LoggerInterface::class);

        // 3. Mock des entités
        $actor = new Actor(1, 'octocat', 'https://github.com/octocat', 'https://avatars.githubusercontent.com/u/1');
        $repo = new Repo(99, 'octo/repo', 'https://github.com/octo/repo');

        // 4. Mock des Repositories
        $actorRepo = $this->createMock(ObjectRepository::class);
        $actorRepo->method('find')->willReturn(null);

        $repoRepo = $this->createMock(ObjectRepository::class);
        $repoRepo->method('find')->willReturn(null);

        $eventRepo = $this->createMock(ObjectRepository::class);
        $eventRepo->method('find')->willReturn(null);

        // 5. Mock de l'EntityManager
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnMap([
            [Actor::class, $actorRepo],
            [Repo::class, $repoRepo],
            [Event::class, $eventRepo],
        ]);

        $em->expects($this->exactly(3))->method('persist');
        $em->expects($this->once())->method('flush');

        // 6. Lance l’import
        $importer = new GitHubEventImporter($em, $logger, $http);
        $count = $importer->importFromUrl('https://example.com/test.json.gz');

        // ✅ Test final
        $this->assertSame(1, $count);
    }
}
