<?php

namespace App\Tests\Func\Services;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Services\EventFilesExtractor;

class EventFilesExtractorFunctionalTest extends KernelTestCase
{
    private EventFilesExtractor $extractor;
    private string $gzFile;
    private string $outputFile;

    protected function setUp(): void
    {
        $this->extractor = self::getContainer()->get(EventFilesExtractor::class);

        $this->gzFile = sys_get_temp_dir() . '/test_file_' . uniqid('', true) . '.json.gz';
        $this->outputFile = sys_get_temp_dir() . '/extracted_file_' . uniqid('', true) . '.json';

        $fixtureFile = __DIR__ . '/../../fixtures/2024-11-12-15.json.gz';
        if (!file_exists($fixtureFile)) {
            $this->fail("Fixture file does not exist: $fixtureFile");
        }
        if (!copy($fixtureFile, $this->gzFile)) {
            $this->fail("Failed to copy fixture file to: $this->gzFile");
        }
    }

    protected function tearDown(): void
    {
        foreach ([$this->gzFile, $this->outputFile] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testExtractValidGzFile(): void
    {
        $result = $this->extractor->extractFile($this->gzFile, $this->outputFile);

        self::assertFileExists($result);
        self::assertGreaterThan(0, filesize($result));
        self::assertSame($this->outputFile, $result);
    }

    public function testExtractFileAlreadyExists(): void
    {
        file_put_contents($this->outputFile, 'existing content');

        $result = $this->extractor->extractFile($this->gzFile, $this->outputFile);

        self::assertSame($this->outputFile, $result);
        self::assertSame('existing content', file_get_contents($this->outputFile));
    }

    public function testExtractFileMissingSource(): void
    {
        $missingFile = sys_get_temp_dir() . '/missing_' . uniqid('', true) . '.gz';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("GZ file does not exist: $missingFile");

        $this->extractor->extractFile($missingFile, $this->outputFile);
    }

    public function testExtractFileWrongExtension(): void
    {
        $wrongFile = sys_get_temp_dir() . '/not_gz_' . uniqid('', true) . '.txt';
        file_put_contents($wrongFile, 'not gzipped');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Not a .gz file: $wrongFile");

        $this->extractor->extractFile($wrongFile, $this->outputFile);

        unlink($wrongFile);
    }

    public function testExtractFileCannotCreateOutput(): void
    {
        $unwritablePath = sys_get_temp_dir() . '/unwritable_dir_' . uniqid('', true);
        mkdir($unwritablePath);
        chmod($unwritablePath, 0555); // Read-only

        $invalidOutput = $unwritablePath . '/output.json';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/GZ extraction failed: .*Permission denied/');
        try {
            $this->extractor->extractFile($this->gzFile, $invalidOutput);
        } finally {
            chmod($unwritablePath, 0755);
            rmdir($unwritablePath);
        }
    }
}
