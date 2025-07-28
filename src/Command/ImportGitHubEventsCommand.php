<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\GitHubEventsImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:import-github-events',
    description: 'Import GH events',
)]
final class ImportGitHubEventsCommand extends Command
{
    public function __construct(
        private readonly GitHubEventsImporter $gitHubEventsImporter,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('start-date', InputArgument::REQUIRED, 'Start Date')
            ->addArgument('end-date', InputArgument::REQUIRED, 'End Date');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startDateArg = $input->getArgument('start-date');
        $endDateArg = $input->getArgument('end-date');

        $violations = $this->validator->validate(
            [
                'start-date' => $startDateArg,
                'end-date' => $endDateArg,
            ],
            new Assert\Collection([
                'start-date' => [new Assert\DateTime(['format' => 'Y-m-d H:i'])],
                'end-date' => [new Assert\DateTime(['format' => 'Y-m-d H:i'])],
            ]),
        );

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errorMessage = $violation->getMessage();
                $output->writeln(sprintf('<error>%s</error>', $errorMessage));
            }

            return self::FAILURE;
        }

        $startDate = new \DateTimeImmutable($startDateArg);
        $endDate = new \DateTimeImmutable($endDateArg);
        $helper = new QuestionHelper();
        $question = new ConfirmationQuestion(
            sprintf(
                'We will process files from %s to %s, Do you want to continue ? [YES/no]  ',
                $startDate->format('Y-m-d H:00'),
                $endDate->format('Y-m-d H:00'),
            ),
            true,
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $this->gitHubEventsImporter->importEvents($startDate, $endDate);

        return self::SUCCESS;
    }
}
