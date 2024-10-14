<?php

declare(strict_types=1);

namespace App\Downloader;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GHArchiveDownloader
{
    private readonly Filesystem $filesystem;

    public function __construct(
        private readonly HttpClientInterface $ghArchiveClient,
        private readonly LoggerInterface $logger,
    )
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * Downloads .json.gz files from the GHArchive API and saves it on our filesystem
     *
     * @return iterable<string> The saved .json.gz files on our filesystem
     */
    public function download(\DateTimeImmutable $from, ?\DateTimeImmutable $to = null): iterable
    {
        $responses = [];
        foreach (new \DatePeriod($from, new \DateInterval('PT1H'), $to ?? $from->add(new \DateInterval('PT1H'))) as $period) {
            $filename = $this->filesystem->tempnam(sys_get_temp_dir(), 'gha_');

            $responses[] = $this->ghArchiveClient->request(
                'GET',
                sprintf('/%s.json.gz', $period->format('Y-m-d-G')),
                [
                    'user_data' => [
                        'filename' => $filename,
                    ],
                ]
            );
        }

        foreach ($this->ghArchiveClient->stream($responses) as $response => $chunk) {
            /** @var string $filename */
            $filename = $response->getInfo('user_data')['filename'];

            try {
                if ($chunk->isLast()) {
                    yield $filename;
                }

                if (!$chunk->isFirst()) {
                    $this->filesystem->appendToFile($filename, $chunk->getContent());
                }
            } catch (TransportExceptionInterface $e) {
                $this->logger->error($e->getMessage(), [
                    'e' => $e,
                ]);
            }
        }
    }
}
