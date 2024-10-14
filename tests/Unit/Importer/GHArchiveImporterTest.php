<?php

declare(strict_types=1);

namespace App\Tests\Unit\Importer;

use App\Downloader\GHArchiveDownloader;
use App\Importer\GHArchiveImporter;
use App\Reader\GHArchiveReader;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @covers \App\Importer\GHArchiveImporter
 */
final class GHArchiveImporterTest extends TestCase
{
    public function testImport(): void
    {
        $expectedRequests = [
            function ($method, $url): MockResponse {
                $this->assertSame('GET', $method);
                $this->assertSame('https://data.gharchive.org/2015-01-01-15.json.gz', $url);

                $json = <<<JSON
{"id": 1}
{"id": 2}
{"id": 3}
JSON;

                return new MockResponse($json);
            },
        ];

        $client = new MockHttpClient($expectedRequests, 'https://data.gharchive.org');
        $downloader = new GHArchiveDownloader($client, new NullLogger());
        $reader = new GHArchiveReader(new Serializer(
            normalizers: [new ObjectNormalizer()],
            encoders: [new JsonEncoder()],
        ));

        $importer = new GHArchiveImporter($downloader, $reader, new MessageBus());
        $count = $importer->import(\DateTimeImmutable::createFromFormat('Y-m-d-H', '2015-01-01-15'));

        self::assertSame(3, $count);
    }
}
