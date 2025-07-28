<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\GitHubEventsCleaner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(
    name: 'app:clean-github-events',
    description: 'Remove GitHub events files',
)]
final class CleanUpGitHubEventsCommand extends Command
{
    public function __construct(
        private readonly GitHubEventsCleaner $gitHubEventsCleaner,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('before-date', InputArgument::REQUIRED, 'delete all files last modified before this Date');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {

            $beforeDate = new \DateTimeImmutable($input->getArgument('before-date'));

        } catch (\Exception) {
            $output->writeln('<error>Invalid date format. Please use a valid date format (e.g., YYYY-MM-DD HH:MM:SS).</error>');

            return self::FAILURE;
        }
        $helper = new QuestionHelper();
        $question = new ConfirmationQuestion(
            sprintf(
                'We will DELETE all files modified before %s, Do you want to continue ? [NO/yes]  ',
                $beforeDate->format('Y-m-d H:i'),
            ),
            false,
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $this->gitHubEventsCleaner->deleteEventsFiles($beforeDate);

        return self::SUCCESS;
    }
}
