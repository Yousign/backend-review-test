<?php

declare(strict_types=1);

namespace App\Parser;

class GzipJsonParser
{
    public function parse(string $sourceFile): \Generator
    {
        $gz = gzopen($sourceFile, 'r');
        if (!$gz) {
            throw new \Exception("Gzip file could not be opened : $sourceFile");
        }

        $buffer = '';
        while (!gzeof($gz)) {
            $buffer .= gzread($gz, 1024);
            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                yield $line;
            }
        }

        gzclose($gz);
    }
}
