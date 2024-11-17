<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\EventInput;
use App\Entity\Event;
use App\Entity\EventType;
use Doctrine\DBAL\Connection;

class DbalWriteEventRepository implements WriteEventRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Met à jour un événement en fonction de l'ID et des données fournies.
     *
     * @param EventInput $authorInput
     * @param int $id
     */
    public function update(EventInput $authorInput, int $id): void
    {
        $sql = <<<SQL
            UPDATE event
            SET comment = :comment
            WHERE id = :id
        SQL;

        $this->connection->executeQuery($sql, [
            'id' => $id,
            'comment' => $authorInput->comment,
        ]);
    }

    /**
     * Sauvegarde un tableau d'événements dans la base de données.
     *
     * @param array $events
     */
    public function saveEvents(array $events): void
    {
        if (empty($events)) {
            return;
        }

        try {
            $this->connection->beginTransaction();

            $this->bulkInsert(
                'actor',
                ['id', 'login', 'url', 'avatar_url'],
                $this->prepareActorData($events),
                ['login', 'url', 'avatar_url']
            );

            $this->bulkInsert(
                'repo',
                ['id', 'name', 'url'],
                $this->prepareRepoData($events),
                ['name', 'url']
            );

            $this->bulkInsert(
                'event',
                ['id', 'type', 'actor_id', 'repo_id', 'payload', 'create_at', 'count', 'comment'],
                $this->prepareEventData($events),
                ['type', 'actor_id', 'repo_id', 'payload', 'create_at', 'count', 'comment']
            );

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new \RuntimeException(sprintf('Failed to save events: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * Méthode générique pour effectuer des insertions en masse avec gestion des conflits.
     *
     * @param string $table
     * @param array $columns
     * @param array $rows
     * @param array $conflicts
     */
    private function bulkInsert(string $table, array $columns, array $rows, array $conflicts): void
    {
        if (empty($rows)) {
            return;
        }

        $batchSize = 100;
        $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $updateClause = implode(', ', array_map(fn($col) => "$col = EXCLUDED.$col", $conflicts));

        for ($i = 0; $i < count($rows); $i += $batchSize) {
            $batch = array_slice($rows, $i, $batchSize);

            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES %s ON CONFLICT (id) DO UPDATE SET %s',
                $table,
                implode(', ', $columns),
                implode(', ', array_fill(0, count($batch), $placeholders)),
                $updateClause
            );

            $flattenedBatch = array_merge(...$batch);

            $this->connection->executeStatement($sql, $flattenedBatch);
        }
    }


    /**
     * Prépare les données des acteurs pour l'insertion en base.
     *
     * @param array $events
     * @return array
     */
    private function prepareActorData(array $events): array
    {
        $uniqueActors = [];
        foreach ($events as $event) {
            $actor = $event->actor();
            $uniqueActors[$actor->id()] = $actor;
        }

        return array_map(
            fn($actor) => [$actor->id(), $actor->login(), $actor->url(), $actor->avatarUrl()],
            $uniqueActors
        );
    }

    /**
     * Prépare les données des dépôts pour l'insertion en base.
     *
     * @param array $events
     * @return array
     */
    private function prepareRepoData(array $events): array
    {
        $uniqueRepos = [];
        foreach ($events as $event) {
            $repo = $event->repo();
            $uniqueRepos[$repo->id()] = $repo;
        }

        return array_map(
            fn($repo) => [$repo->id(), $repo->name(), $repo->url()],
            $uniqueRepos
        );
    }

    /**
     * Prépare les données des événements pour l'insertion en base.
     *
     * @param array $events
     * @return array
     */
    private function prepareEventData(array $events): array
    {
        return array_map(
            fn(Event $event) => [
                $event->id(),
                $event->type(),
                $event->actor()->id(),
                $event->repo()->id(),
                json_encode($event->payload()),
                $event->createAt()->format('Y-m-d H:i:s'),
                $event->type() === EventType::COMMIT ? ($event->payload()['size'] ?? 1) : 1,
                $event->getComment()
            ],
            $events
        );
    }
}
