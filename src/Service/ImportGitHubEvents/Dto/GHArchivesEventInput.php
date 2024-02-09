<?php

namespace App\Service\ImportGitHubEvents\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class GHArchivesEventInput
{
    /**
     * @Assert\Positive
     */
    public string $id;

    /**
     * @Assert\NotBlank
     */
    public string $type;

    public GHArchivesActorInput $actor;

    public GHArchivesRepoInput $repo;

    /**
     * @Assert\NotBlank
     */
    public array $payload;

    /**
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    public string $createdAt;
}
