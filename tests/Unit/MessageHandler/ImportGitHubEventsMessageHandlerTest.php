<?php

namespace App\Tests\Unit\MessageHandler;

use App\Managers\EventManager;
use App\Message\ImportGitHubEventsMessage;
use App\MessageHandler\ImportGitHubEventsMessageHandler;
use App\Services\EventFilesDownloader;
use App\Services\EventFilesExtractor;
use App\Services\EventFilesReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImportGitHubEventsMessageHandlerTest extends TestCase
{
    /**
     * @var EventManager&MockObject
     */
    public EventManager $eventManager;


    /**
     * @var ParameterBagInterface&MockObject
     */
    public ParameterBagInterface $parameterBag;

    /**
     * @var EventFilesDownloader&MockObject
     */
    public EventFilesDownloader $eventFilesDownloader;

    /**
     * @var EventFilesExtractor&MockObject
     */
    public EventFilesExtractor $eventFilesExtractor;

    /**
     * @var EventFilesReader&MockObject
     */
    public EventFilesReader $eventFilesReader;


    /**
     * @var LoggerInterface&MockObject
     */
    public LoggerInterface $logger;

    public ImportGitHubEventsMessage $message;

    protected function setUp(): void
    {
        $this->eventManager = $this->createMock(EventManager::class);
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->eventFilesDownloader = $this->createMock(EventFilesDownloader::class);
        $this->eventFilesExtractor = $this->createMock(EventFilesExtractor::class);
        $this->eventFilesReader = $this->createMock(EventFilesReader::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->message = new ImportGitHubEventsMessage('2023-10-01-12');
    }

    public function testImportGitHubEventsMessageHandler(): void
    {
        $this->eventFilesDownloader->expects($this->once())
            ->method('downloadFile');
        $this->eventFilesExtractor->expects($this->once())
            ->method('extractFile');

        $debugCalls = [];
        $this->logger->expects($this->exactly(2))
            ->method('debug')
            ->willReturnCallback(function (string $msg) use (&$debugCalls) {
                $debugCalls[] = $msg;
            });
        $importGitHubEventsMessageHandler = new ImportGitHubEventsMessageHandler(
            $this->eventManager,
            $this->parameterBag,
            $this->eventFilesDownloader,
            $this->eventFilesExtractor,
            $this->eventFilesReader,
            $this->logger,
        );
        $importGitHubEventsMessageHandler($this->message);

        self::assertEquals([
            'Starting GitHub events import process 2023-10-01-12',
            'GitHub events import process Finished 2023-10-01-12',
        ], $debugCalls);
    }
}
