<?php

namespace App\Service\ImportGitHubEvents\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class GHArchivesRepoInput
{
    /**
     * @Assert\Positive
     */
    public int $id;

    /**
     * @Assert\NotBlank
     */
    public string $name;

    /**
     * @Assert\Url
     */
    public string $url;
}
