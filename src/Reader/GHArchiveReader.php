<?php

declare(strict_types=1);

namespace App\Reader;

use App\Dto\GHArchiveEvent;
use App\Exception\CouldNotOpenGHArchiveException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

final class GHArchiveReader
{
    private Filesystem $filesystem;

    public function __construct(
        private readonly SerializerInterface $serializer,
    )
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * Reads the file line by line as the expected format is the JSON Lines text format
     *
     * @link https://jsonlines.org/
     *
     * @param string $filename Path to the .json.gz file
     *
     * @return iterable<GHArchiveEvent>
     *
     * @throws CouldNotOpenGHArchiveException if failed to open the .json.gz file
     */
    public function readAndDelete(string $filename): iterable
    {
        $resource = gzopen($filename, 'r');
        if (false === $resource) {
            throw new CouldNotOpenGHArchiveException($filename);
        }

        while (!gzeof($resource)) {
            $row = gzgets($resource);
            if (false === $row) {
                continue;
            }

            yield $this->serializer->deserialize($row, GHArchiveEvent::class, 'json');
        }

        gzclose($resource);

        $this->filesystem->remove($filename);
    }
}
