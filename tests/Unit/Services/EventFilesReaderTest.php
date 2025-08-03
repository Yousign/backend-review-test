<?php

namespace App\Tests\Unit\Services;

use App\Services\EventFilesReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EventFilesReaderTest extends TestCase
{
    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;
    /**
     * @var ParameterBagInterface&MockObject
     */
    private ParameterBagInterface $parameterBag;
    private EventFilesReader $eventFilesReader;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->eventFilesReader = new EventFilesReader($this->logger, $this->parameterBag);
    }

    public function testReadLinesFromFileReadsEachLineAndTrims(): void
    {
        $fileContent = "{\"id\": 1,\"type\": \"PullRequestEvent\"}  \n{\"id\": 2,\"type\": \"IssueCommentEvent\"}\n  {\"id\": 3,\"type\": \"WatchEvent\"}  \n";
        $expectedLines = [
            ['id' => 1, 'type' => 'PullRequestEvent'],
            ['id' => 2, 'type' => 'IssueCommentEvent'],
            ['id' => 3, 'type' => 'WatchEvent'],
        ];

        $testDir = __DIR__ . '/' . uniqid('', true) . 'test_data';
        $testFile = $testDir . '/lines_test.txt';
        if (!is_dir(dirname($testFile))) {
            mkdir(dirname($testFile), 0777, true);
        }
        file_put_contents($testFile, $fileContent);


        $this->parameterBag->expects($this->exactly(2))
            ->method('get')->willReturnCallback(function ($key) {
                return match ($key) {
                    'app.supported_types' => ['PushEvent', 'PullRequestEvent', 'IssuesEvent', 'IssueCommentEvent', 'WatchEvent'],
                    'app.import_only_supported_types' => true,
                    default => $this->fail('Unsupported parameter key: ' . $key),
                };
            });

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Reading lines from file', ['file_path' => $testFile]);

        $lines = [];
        foreach ($this->eventFilesReader->readLinesFromFile($testFile) as $line) {
            $lines[] = $line;
        }

        self::assertSame($expectedLines, $lines);

        unlink($testFile);
        rmdir($testDir);
    }
}
