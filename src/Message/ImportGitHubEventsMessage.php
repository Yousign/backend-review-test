<?php

namespace App\Message;

class ImportGitHubEventsMessage
{
    public function __construct(
        private string $dateHour,
    ) {
    }
    public function getDateHour(): string
    {
        return $this->dateHour;
    }
}
