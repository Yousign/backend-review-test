<?php

namespace App\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class EventFilesExtractor
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $logger,
    ) {}

    public function extractFile(string $filePath, string $outputPath): string
    {
        $this->logger->debug('Starting file extraction', [
            'source_path' => $filePath,
            'output_path' => $outputPath,
        ]);

        if ($this->filesystem->exists($outputPath)) {
            $this->logger->debug('Extracted file already exists, skipping extraction', [
                'output_path' => $outputPath,
            ]);

            return $outputPath;
        }
        if (!$this->filesystem->exists($filePath)) {
            throw new \RuntimeException("GZ file does not exist: $filePath");
        }
        if (!str_ends_with(strtolower($filePath), '.gz')) {
            throw new \RuntimeException("Not a .gz file: $filePath");
        }
        try {
            $gzHandle = gzopen($filePath, 'rb');
            if (false === $gzHandle) {
                throw new \RuntimeException('Failed to open GZ file');
            }
            $this->filesystem->mkdir(dirname($outputPath));
            $outputHandle = fopen($outputPath, 'wb');
            if (false === $outputHandle) {
                throw new \RuntimeException('Failed to create output file');
            }
            $bytesExtracted = 0;
            while (!gzeof($gzHandle)) {
                $chunk = gzread($gzHandle, 65536);
                if (false === $chunk || false === fwrite($outputHandle, $chunk)) {
                    throw new \RuntimeException('Decompression failed');
                }
                $bytesExtracted += strlen($chunk);
            }
            $this->logger->debug('File extraction completed successfully', [
                'source_path' => $filePath,
                'output_path' => $outputPath,
                'bytes_extracted' => $bytesExtracted,
            ]);

            return $outputPath;
        } catch (\Throwable $e) {
            if ($this->filesystem->exists($outputPath)) {
                $this->logger->debug('Cleaning up failed extraction output file', ['output_path' => $outputPath]);
                $this->filesystem->remove($outputPath);
            }
            throw new \RuntimeException('GZ extraction failed: ' . $e->getMessage(), previous: $e);
        } finally {
            if (isset($gzHandle) && is_resource($gzHandle)) {
                gzclose($gzHandle);
            }
            if (isset($outputHandle) && is_resource($outputHandle)) {
                fclose($outputHandle);
            }
        }
    }
}
