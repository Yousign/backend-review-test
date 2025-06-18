<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportGitHubEventsCommandTest extends KernelTestCase
{
    public function testExecuteWithInvalidUrl(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:import-github-events');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'url' => null, // simulate missing or invalid URL
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('❌ Invalid URL provided', $commandTester->getDisplay());
    }

    public function testExecuteWithFakeValidUrl(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:import-github-events');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'url' => 'https://example.com/test.json.gz',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertSame(1, $exitCode); // because importer will fail with fake URL
        $this->assertStringContainsString('❌ Error during import:', $output);
    }
}
