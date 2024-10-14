<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\ImportGitHubEventsCommand;
use App\Importer\GHArchiveImporterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \App\Command\ImportGitHubEventsCommand
 */
final class ImportGitHubEventsCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $commandTester = new CommandTester(new ImportGitHubEventsCommand($this->createDummyImporter()));

        $commandTester->execute([
            'from' => '2015-01-01-15',
        ]);

        $commandTester->assertCommandIsSuccessful();
        self::assertStringContainsString('Dispatched messages to import 10 events ', $commandTester->getDisplay());
    }

    /**
     * @return iterable<array<array<string>, string>
     */
    public static function provideInvalidArguments(): iterable
    {
        yield 'wrong first argument' => [
            ['from' => '20-04-2015'],
            'Wrong date format for the first argument.',
        ];

        yield 'wrong second argument' => [
            [
                'from' => '2015-01-01-10',
                'to' => '20-04-2015',
            ],
            'Wrong date format for the second argument.',
        ];
    }

    /**
     * @param array<string> $arguments
     *
     * @dataProvider provideInvalidArguments
     */
    public function testArgumentFromDateFormat(array $arguments, string $expectedOutput): void
    {
        $commandTester = new CommandTester(new ImportGitHubEventsCommand($this->createDummyImporter()));

        $commandTester->execute($arguments);

        self::assertStringContainsString($expectedOutput, $commandTester->getDisplay());
    }

    private function createDummyImporter(): GHArchiveImporterInterface
    {
        return new class implements GHArchiveImporterInterface {
            public function import(\DateTimeImmutable $from, ?\DateTimeImmutable $to = null): int
            {
                return 10;
            }
        };
    }
}
