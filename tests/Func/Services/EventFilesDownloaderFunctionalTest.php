<?php

namespace App\Tests\Func\Services;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Services\EventFilesDownloader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EventFilesDownloaderFunctionalTest extends KernelTestCase
{
    private EventFilesDownloader $downloader;
    private string $tempFile;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->tempFile = sys_get_temp_dir() . '/event_file_' . uniqid('', true) . '.json';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testDownloadFileFromPublicUrl(): void
    {
        $mockResponse = new MockResponse(
            'abc',
            [
                'http_code' => 200,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        );

        $this->downloader = new EventFilesDownloader(
            self::getContainer()->get(Filesystem::class),
            new MockHttpClient($mockResponse),
            self::getContainer()->get('logger'),
        );
        $this->downloader->downloadFile('https://fakeurl.com', $this->tempFile);

        self::assertFileExists($this->tempFile);
        self::assertGreaterThan(0, filesize($this->tempFile));
        self::assertEquals('abc', file_get_contents($this->tempFile));
    }

    public function testDownloadFileFail(): void
    {

        $mockResponse = new MockResponse(
            'error',
            [
                'http_code' => 404,
            ],
        );

        $this->downloader = new EventFilesDownloader(
            self::getContainer()->get(Filesystem::class),
            new MockHttpClient($mockResponse),
            self::getContainer()->get('logger'),
        );

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Failed to download file from: https://fakeurl.com');

        $this->downloader->downloadFile('https://fakeurl.com', $this->tempFile);
    }
}
