<?php

declare(strict_types=1);

namespace App\Exception;

class CouldNotOpenGHArchiveException extends \RuntimeException
{
    public function __construct(string $filename)
    {
        parent::__construct(sprintf('Could not open "%s".', $filename));
    }
}
