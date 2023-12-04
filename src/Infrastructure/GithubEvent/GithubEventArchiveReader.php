<?php

namespace App\Infrastructure\GithubEvent;

final class GithubEventArchiveReader implements GithubEventArchiveReaderInterface
{
    public function read($filename)
    {
        // @TODO !!! clean
        $fp = gzopen($filename, 'r');
        while (($buffer = gzgets($fp, 4096)) !== false) {
            yield json_decode($buffer, true);
        }

        gzclose($fp);
    }
}
