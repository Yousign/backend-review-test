<?php

namespace App\Tests\Func\Services;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Services\GitHubEventsCleaner;

class GitHubEventsCleanerFunctionalTest extends KernelTestCase
{
    private GitHubEventsCleaner $cleaner;
    private string $extractionDir;
    private string $downloadDir;
    private array $testFiles = [];

    protected function setUp(): void
    {

        self::bootKernel();
        $this->cleaner = self::getContainer()->get(GitHubEventsCleaner::class);

        $parameterBag = self::getContainer()->get('parameter_bag');
        $this->extractionDir = $parameterBag->get('app.extraction_directory');
        $this->downloadDir = $parameterBag->get('app.download_directory');
        // directories exist
        if (!is_dir($this->extractionDir)) {
            mkdir($this->extractionDir, 0777, true);
        }
        if (!is_dir($this->downloadDir)) {
            mkdir($this->downloadDir, 0777, true);
        }

        // Create test files
        $this->createTestFile($this->downloadDir, 'recent_min_event.json.gz', '-30 minutes');
        $this->createTestFile($this->downloadDir, 'minus_three_days_file.json.gz', '-3 days');

        $this->createTestFile($this->extractionDir, 'recent_hour_event.json', '-1 hour');
        $this->createTestFile($this->extractionDir, 'minus_two_days_file.json', '-2 days');
    }

    protected function tearDown(): void
    {
        foreach ($this->testFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        foreach ([$this->extractionDir, $this->downloadDir] as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    public function testDeleteEventsFiles(): void
    {
        $beforeDate = new \DateTime('-1 day');
        $this->cleaner->deleteEventsFiles($beforeDate);

        self::assertFileDoesNotExist($this->extractionDir . '/minus_two_days_file.json');
        self::assertFileDoesNotExist($this->downloadDir . '/minus_three_days_file.json.gz');
        self::assertFileExists($this->extractionDir . '/recent_hour_event.json');
        self::assertFileExists($this->downloadDir . '/recent_min_event.json.gz');
    }

    private function createTestFile(string $dir, string $filename, string $modificationTime): void
    {
        $filePath = $dir . '/' . $filename;
        file_put_contents($filePath, 'test content');
        touch($filePath, strtotime($modificationTime));
        $this->testFiles[] = $filePath;
    }
}
