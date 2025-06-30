<?php

namespace App\Tests\Func\Command;

use App\Service\ArchiveDownloader;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class ImportGitHubEventsCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        self::bootKernel();

        $kernel = self::$kernel;
        $application = new Application($kernel);
        $command = $application->find('app:import-github-events');
        $commandTester = new CommandTester($command);
        $events = $this->givenEvents();
        $filename = $this->createGzipFile($events);

        $archiveDownloader = static::getContainer()->get(ArchiveDownloader::class);
        $archiveDownloader->setTestFile($filename);

        $commandTester->execute([
            'date' => '2025-01-01-15',
        ]);

        $commandTester->assertCommandIsSuccessful();

        (new Filesystem())->remove($filename);
    }

    private function createGzipFile(array $events): string
    {
        $filename = tempnam(sys_get_temp_dir(), 'gh') . '.json.gz';
        $gz = gzopen($filename, 'w');

        foreach ($events as $event) {
            gzwrite($gz, json_encode($event) . "\n");

        }

        gzclose($gz);

        return $filename;
    }

    private function givenEvents(): array
    {
        return [
            [
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
            ],
        ];
    }
}
