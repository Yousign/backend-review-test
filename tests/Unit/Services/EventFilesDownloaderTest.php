<?php

namespace App\Tests\Unit\Services;

use App\Services\EventFilesDownloader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class EventFilesDownloaderTest extends TestCase
{
    public Filesystem|MockObject $filesystem;

    /**
     * @var HttpClientInterface&MockObject
     */
    public HttpClientInterface $httpClient;

    /**
     * @var LoggerInterface&MockObject
     */
    public LoggerInterface $logger;
    public EventFilesDownloader $downloader;
    public string $tempDir;

    public function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->downloader = new EventFilesDownloader(
            $this->filesystem,
            $this->httpClient,
            $this->logger,
        );

        $this->tempDir = sys_get_temp_dir() . '/event_files_downloader_test_' . uniqid('', true);
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    public function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->filesystem = new Filesystem();
            $this->filesystem->remove($this->tempDir);
        }
    }

    public function testDownloadFileSuccessfully(): void
    {
        $url = 'testUrl//file.zip';
        $targetDirectory = $this->tempDir . '/file.zip';
        $content = 'test file content';

        $response = $this->createMock(ResponseInterface::class);
        $responseStream = $this->createMock(ResponseStreamInterface::class);
        $chunk = $this->createMock(ChunkInterface::class);

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($targetDirectory)
            ->willReturn(false);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', $url)
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->filesystem->expects($this->once())
            ->method('mkdir')
            ->with(dirname($targetDirectory));

        $this->httpClient->expects($this->once())
            ->method('stream')
            ->with($response)
            ->willReturn($responseStream);

        $responseStream->expects($this->once())
            ->method('rewind');

        $responseStream->expects($this->exactly(2))
            ->method('valid')
            ->willReturnOnConsecutiveCalls(true, false);

        $responseStream->expects($this->once())
            ->method('current')
            ->willReturn($chunk);

        $responseStream->expects($this->once())
            ->method('next');

        $chunk->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $this->logger->expects($this->exactly(2))
            ->method('debug')
            ->willReturnCallback(function ($message, $context = []) use ($url, $targetDirectory) {
                static $callCount = 0;
                ++$callCount;

                if (1 === $callCount) {
                    self::assertEquals('Starting file downloading', $message);
                    self::assertEquals(['source_url' => $url, 'output_path' => $targetDirectory], $context);
                } elseif (2 === $callCount) {
                    self::assertEquals('File downloaded successfully', $message);
                    self::assertEquals(['output_path' => $targetDirectory], $context);
                }
            });

        $this->downloader->downloadFile($url, $targetDirectory);

        self::assertFileExists($targetDirectory);
        self::assertEquals($content, file_get_contents($targetDirectory));
    }

    public function testDownloadFileWhenFileAlreadyExists(): void
    {
        $url = 'testUrl//file.zip';
        $targetDirectory = $this->tempDir . '/existing_file.zip';

        file_put_contents($targetDirectory, 'existing content');

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($targetDirectory)
            ->willReturn(true);

        $this->httpClient->expects($this->never())
            ->method('request');

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('Starting file downloading', ['source_url' => $url, 'output_path' => $targetDirectory]);

        $this->downloader->downloadFile($url, $targetDirectory);

        self::assertEquals('existing content', file_get_contents($targetDirectory));
    }

    public function testDownloadFileWithHttpError(): void
    {
        $url = 'testUrl/file.zip';
        $targetDirectory = $this->tempDir . '/file.zip';

        $response = $this->createMock(ResponseInterface::class);

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($targetDirectory)
            ->willReturn(false);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', $url)
            ->willReturn($response);

        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(404);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('Starting file downloading', ['source_url' => $url, 'output_path' => $targetDirectory]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Failed to download file from: $url");

        $this->downloader->downloadFile($url, $targetDirectory);
    }
}
