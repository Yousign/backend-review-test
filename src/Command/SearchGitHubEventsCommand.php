<?php

namespace App\Command;

use App\Action\SearchHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:search-github-events')]
final class SearchGitHubEventsCommand extends Command
{
    public function __construct(
        private readonly SearchHandler $searchHandler
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import GH events')
            ->addArgument('date', InputArgument::REQUIRED, 'The date for the events to search (format: Y-m-d)')
            ->addArgument('keyword', InputArgument::REQUIRED, 'The associated keyword for the events to search ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $arguments = $this->resolveArguments($input->getArguments());
            $data = $this->searchHandler->search($arguments);
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::INVALID;
        }

        return Command::SUCCESS;
    }

    private function resolveArguments(array $arguments)
    {
        $arguments = array_filter($arguments);
        try {
            if (isset($arguments['keyword'], $arguments['date'])) {
                return [
                    'date' => (new \DateTimeImmutable($arguments['date']))->format('Y-m-d'),
                    'keyword' => $arguments['keyword'],
                ];
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid date format specified. Format: Y-m-d', (int)$e->getCode(), $e);
        }
        throw new \InvalidArgumentException('Missing date or keyword');
    }
}
