<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\GitHubEventsImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
class ImportGitHubEventsCommand extends Command
{
    protected static $defaultName = 'app:import-github-events';

    public function __construct(private readonly GitHubEventsImporter $gitHubEventsImporter, private readonly ValidatorInterface $validator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import GH events')
            ->addArgument('start-date', InputArgument::REQUIRED, 'Start Date')
            ->addArgument('end-date', InputArgument::REQUIRED, 'End Date')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $violations = $this->validator->validate(
            ["start-date"=>$input->getArgument('start-date'), "end-date"=>$input->getArgument('end-date')],
            new Assert\Collection([
                "start-date"=> [new Assert\DateTime(['format' => 'Y-m-d H:i'])],
                "end-date"=> [new Assert\DateTime(['format' => 'Y-m-d H:i'])]
            ]),

        );
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $output->writeln(sprintf('<error>%s</error>', $violation->getMessage()));
            }
            return self::FAILURE;
        }

        $startDate = $input->getArgument('start-date');
        $endDate = $input->getArgument('end-date');


        $this->gitHubEventsImporter->importEvents(
            new \DateTimeImmutable($startDate),
            new \DateTimeImmutable($endDate)
        );
        return self::SUCCESS;
    }
}
