<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\GhArchiveHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
#[AsCommand(name: 'app:import-github-events')]
class ImportGitHubEventsCommand extends Command
{
    private const GHARCHIVE_HOST = 'https://data.gharchive.org/';

    private GhArchiveHandler $ghArchiveHandler;

    public function __construct(GhArchiveHandler $ghArchiveHandler)
    {
        parent::__construct();
        $this->ghArchiveHandler = $ghArchiveHandler;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import GH events')
            ->addOption('date', '', InputOption::VALUE_OPTIONAL, 'Date to retrieve (default to today)', date('Y-m-d'))
            ->addOption('start', '', InputOption::VALUE_OPTIONAL, 'Starting hour of data retrieval')
            ->addOption('end', '',InputOption::VALUE_OPTIONAL, 'Ending hour of data retrieval')
            ->addOption('all-day', '',InputOption::VALUE_NONE, 'Tells process to retrieve all hours')
            ->addOption('hour', '',InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Use this option to ad hours as you like, can\'t be used in combination with startHour or endHour')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $options = $input->getOptions();

        if (!empty($options['hour'])) {
            if(!empty($options['start']) || !empty($options['end'])) {
                $output->writeln('<comment>addHour can\'t be used in combination with startHour or endHour</comment>');
                $output->writeln('<comment>Ending command</comment>');
                return Command::INVALID;
            }
            $hours = $options['hour'];
        } elseif($options['all-day'] === false) {
            if (empty($options['start']) || empty($options['end'])) {
                $output->writeln('<comment>You have to add startHour and endHour option or addHour to start the process</comment>');
                $output->writeln('<comment>Ending command</comment>');
                return Command::INVALID;
            }
            $start = $options['start'];
            $end = $options['end'];
            if ($start > $end) {
                $output->writeln('<comment>Seems like you switched hours, reversing order</comment>');
                $end = $options['start'];
                $start = $options['end'];
            }
            $hours = range($start, $end);
        } else {
            $hours = range(0, 23);
        }

        $output->writeln("");
        $output->writeln("Starting GHArchive data retrieval");
        try {
            $archives = $this->curlDownloadArchives($output, $options['date'], $hours);
        } catch (\Exception $e) {
            $output->writeln('<error>Something went wrong => ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        foreach ($archives as $cpt => $archive) {
            try {
                $output->writeln('<info>Opening file ' . $archive . '</info>');
                $progressBar = new ProgressBar($output);
                $progressBar->setFormat('%bar% %message%');
                $progressBar->setMessage('<comment>Processing lines</comment>');
                $progressBar->display();
                $this->ghArchiveHandler->processArchive($archive, $progressBar);
                $progressBar->finish();
                $output->writeln('');
            } catch (\Exception $e) {
                $output->writeln('<error>Something went wrong with the following archive : ' . $archive . ' | Error => ' . $e->getMessage() . '</error>');

            }
            if ($cpt < (count($archives) - 1)) {
                $output->writeln('Continuing to next archive');
            } else {
                $output->writeln('No more archives to process');
            }
            $output->writeln('');
        }

        $output->writeln("End of GHArchive data retrieval");
        return Command::SUCCESS;
    }


    /**
     * Downloads archive from GH Archive based on specified date and hours
     *
     * @param OutputInterface $output
     * @param string $date
     * @param array $hours
     * @return array
     */
    private function curlDownloadArchives(OutputInterface $output, string $date, array $hours): array
    {
        $downloadedArchives = [];
        foreach ($hours as $hour) {
            $url = self::GHARCHIVE_HOST . $date . '-' . $hour . '.json.gz';
            $file_path = '/tmp/' . basename($url);

            // Open the URL and get the contents
            $curl = curl_init($url);
            // Adding necessary Curl Options for download progress tracking
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_NOPROGRESS, false);
            // Opening file to store download into
            $fh = fopen($file_path, 'wb');
            curl_setopt($curl, CURLOPT_FILE, $fh);

            $output->writeln('Starting download of <comment>' . $url . '</comment>');
            $progressBar = new ProgressBar($output);
            $progressBar->setFormat(' [%bar%] <info>%percent:3s%% </info>');
            $progressBar->setBarCharacter('<info>=</info>');
            $progressBar->setEmptyBarCharacter('=');
            $progressBar->setProgressCharacter('<info>=></info>');
            $progressBar->start();

            // Using Curl progress callback to track download advance
            curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, function ($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($progressBar, $output) {
                $progressBar->setMessage('<info>Downloading...</info>');
                $progressBar->setMaxSteps($downloadSize);
                $progressBar->setProgress($downloaded);
            });

            // Closing all resources and streams
            curl_exec($curl);
            curl_close($curl);
            fclose($fh);
            $progressBar->finish();

            // Returning all archives at once so we store them here
            $downloadedArchives[] = $file_path;

            $output->writeln('End of download for <comment>' . $url . '</comment>');
            $output->writeln('');
        }
        return $downloadedArchives;
    }
}
