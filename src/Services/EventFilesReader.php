<?php

namespace App\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EventFilesReader
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ParameterBagInterface $parameterBag,
    ) {}

    public function readLinesFromFile(string $extractionFilePath): \Generator
    {
        $this->logger->info('Reading lines from file', ['file_path' => $extractionFilePath]);
        $handle = fopen($extractionFilePath, 'rb');
        if (!$handle) {
            throw new \RuntimeException("Could not open the file: {$extractionFilePath}");
        }
        try {
            $import_only_supported_types = $this->parameterBag->get('app.import_only_supported_types');
            $supported_types = $this->parameterBag->get('app.supported_types');
            while (($line = fgets($handle)) !== false) {
                $line = json_decode($this->sanitize($line), true);
                if ($import_only_supported_types && !in_array($line['type'], $supported_types, true)) {
                    continue;
                }
                yield $line;
            }
        } finally {
            fclose($handle);
        }
    }

    private function sanitize(string $line): string
    {
        // sometimes the line contains a \uXXXX unicode escape sequence that is not escaped properly
        // this regex will escape the \uXXXX sequence if it is not already escaped
        $line = preg_replace('/([^\\\\])(\\\\u[0-9a-f]{1,4})/mi', '${1}\\\\${2}', $line);

        return trim($line);

    }
}
