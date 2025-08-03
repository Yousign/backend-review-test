<?php

namespace App\Tests\Unit\Command;

use App\Command\ImportGitHubEventsCommand;
use App\Services\GitHubEventsImporter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportGitHubEventsCommandTest extends TestCase
{
    /**
     * @var GitHubEventsImporter&MockObject
     */
    private GitHubEventsImporter $importerMock;
    /**
     * @var ValidatorInterface&MockObject
     */
    private ValidatorInterface $validatorMock;

    protected function setUp(): void
    {
        $this->importerMock = $this->createMock(GitHubEventsImporter::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

    }

    public function testExecuteWithValidDatesAndConfirmation(): void
    {
        $this->importerMock->expects($this->once())
            ->method('importEvents');

        $this->validatorMock->method('validate')
            ->willReturn(new ConstraintViolationList());

        $command = new ImportGitHubEventsCommand($this->importerMock, $this->validatorMock);
        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);

        $tester->execute([
            'start-date' => '2025-08-01 00:00',
            'end-date' => '2025-08-02 00:00',
        ]);

        self::assertEquals(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('Do you want to continue', $tester->getDisplay());
    }

    public function testExecuteWithInvalidDates(): void
    {
        $violation = new ConstraintViolation(
            'Invalid date format',
            '',
            [],
            '',
            'start-date',
            'invalid-date',
        );

        $this->validatorMock->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $command = new ImportGitHubEventsCommand($this->importerMock, $this->validatorMock);
        $tester = new CommandTester($command);

        $tester->execute([
            'start-date' => 'invalid-date',
            'end-date' => '2025-08-02 00:00',
        ]);

        self::assertEquals(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('Invalid date format', $tester->getDisplay());
    }

    public function testCommandExitsEarlyWhenUserSaysNo(): void
    {
        $this->importerMock->expects($this->never())
            ->method('importEvents');

        $this->validatorMock->method('validate')
            ->willReturn(new ConstraintViolationList());

        $command = new ImportGitHubEventsCommand($this->importerMock, $this->validatorMock);
        $tester = new CommandTester($command);
        $tester->setInputs(['no']);

        $tester->execute([
            'start-date' => '2025-08-01 00:00',
            'end-date' => '2025-08-02 00:00',
        ]);

        self::assertEquals(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('[YES/no]', $tester->getDisplay());
    }
}
