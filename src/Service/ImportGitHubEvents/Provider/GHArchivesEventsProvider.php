<?php

declare(strict_types=1);

namespace App\Service\ImportGitHubEvents\Provider;

use App\Service\ImportGitHubEvents\Dto\GHArchivesEventInput;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GHArchivesEventsProvider
{
    public function __construct(
        private HttpClientInterface $client,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @return array<GHArchivesEventInput>
     */
    public function fetch(string $query): array
    {
        $response = $this->client->request('GET', "https://data.gharchive.org/$query.json.gz");

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException("Error from gharchive: {$response->getStatusCode()}");
        }

        $file = gzdecode(data: $response->getContent());

        $lines = array_filter(
            explode(separator: "\n", string: $file),
            static fn (string $line) => strpos(haystack: $line, needle: '{') === 0,
        );

        return array_map(
            fn (string $line) => $this->serializer->deserialize($line, GHArchivesEventInput::class, 'json'),
            $lines,
        );
    }
}
