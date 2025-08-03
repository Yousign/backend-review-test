<?php

namespace App\Tests\Unit\Command;

use App\Command\CleanUpGitHubEventsCommand;
use App\Services\GitHubEventsCleaner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class CleanUpGitHubEventsCommandTest extends TestCase
{
    /**
     * @var GitHubEventsCleaner&MockObject
     */
    private GitHubEventsCleaner $cleanerMock;
    private CommandTester $tester;
    private CleanUpGitHubEventsCommand $command;

    protected function setUp(): void
    {

        $this->cleanerMock = $this->createMock(GitHubEventsCleaner::class);
        $this->command = new CleanUpGitHubEventsCommand($this->cleanerMock);
        $this->tester = new CommandTester($this->command);
    }

    public function testExecuteWithValidDateAndConfirmation(): void
    {
        $this->cleanerMock->expects($this->once())
            ->method('deleteEventsFiles');

        $this->tester->setInputs(['yes']);

        $this->tester->execute([
            'before-date' => '2025-08-01 00:00:00',
        ]);

        self::assertEquals(Command::SUCCESS, $this->tester->getStatusCode());
        self::assertStringContainsString('Do you want to continue', $this->tester->getDisplay());
    }

    public function testCommandExitsEarlyWhenUserSaysNo(): void
    {
        $questionHelper = $this->createMock(QuestionHelper::class);
        $questionHelper->method('ask')
            ->willReturn(false);

        $this->tester = new CommandTester($this->command);
        $this->tester->execute([
            'before-date' => '2025-08-01 00:00:00',
        ]);
        self::assertEquals(Command::SUCCESS, $this->tester->getStatusCode());
        self::assertStringContainsString('[NO/yes] ', $this->tester->getDisplay());
    }

    public function testExecuteWithInvalidDate(): void
    {
        $this->tester->execute([
            'before-date' => 'invalid-date',
        ]);

        self::assertEquals(Command::FAILURE, $this->tester->getStatusCode());
        self::assertStringContainsString('Invalid date format', $this->tester->getDisplay());
    }
}
