<?php

namespace App\Tests\Unit\Service\ImportGitHubEvents\Transformer;

use App\Entity\Event;
use App\Service\ImportGitHubEvents\Dto\GHArchivesActorInput;
use App\Service\ImportGitHubEvents\Dto\GHArchivesEventInput;
use App\Service\ImportGitHubEvents\Dto\GHArchivesRepoInput;
use App\Service\ImportGitHubEvents\Transformer\GHArchivesEventsTransformer;
use PHPUnit\Framework\TestCase;

class GHArchivesEventsTransformerTest extends TestCase
{
    private function getGHArchivesEventInput(): GHArchivesEventInput
    {
        $actorInput = new GHArchivesActorInput();
        $actorInput->id = 123;
        $actorInput->login = 'petroav';
        $actorInput->url = 'https://api.github.com/users/petroav';
        $actorInput->avatarUrl = "https://avatars.githubusercontent.com/u/665991?";

        $repoInput = new GHArchivesRepoInput();
        $repoInput->id = 456;
        $repoInput->name = 'petroav/6.828';
        $repoInput->url = 'https://api.github.com/repos/petroav/6.828';

        $eventInput = new GHArchivesEventInput();
        $eventInput->id = '789';
        $eventInput->createdAt = '2015-01-01T15:00:00Z';
        $eventInput->type = 'PushEvent';
        $eventInput->payload = ["ref" => "master"];
        $eventInput->actor = $actorInput;
        $eventInput->repo = $repoInput;

        return $eventInput;
    }

    public function testCanTransformValidGHArchivesEvents(): void
    {
        $transformer = new GHArchivesEventsTransformer();
        $eventInput = $this->getGHArchivesEventInput();
        $results = $transformer->transformToEvents([
            $eventInput
        ]);

        self::assertNotEmpty($results);
        self::assertInstanceOf(Event::class, $results[0]);
        self::assertSame(789, $results[0]->id());
    }
}
