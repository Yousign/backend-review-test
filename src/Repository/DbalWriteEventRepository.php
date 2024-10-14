<?php

namespace App\Repository;

use App\Dto\EventInput;
use App\Entity\EventType;
use Doctrine\DBAL\Connection;

class DbalWriteEventRepository implements WriteEventRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function insertGHArchiveEvents(iterable $events): void
    {
        /**
         * Disable SQL Logger.
         *
         * @see https://www.doctrine-project.org/projects/doctrine-orm/en/3.2/reference/batch-processing.html
         */
        $this->connection->getConfiguration()->setMiddlewares([]);

        $insertActorStatement = $this->connection->prepare(<<<SQL
            INSERT INTO actor(id, login, url, avatar_url)
            VALUES (:id, :login, :url, :avatar_url)
            ON CONFLICT DO NOTHING
SQL);

        $insertRepoStatement = $this->connection->prepare(<<<SQL
            INSERT INTO repo(id, name, url)
            VALUES (:id, :name, :url)
            ON CONFLICT DO NOTHING
SQL);

        $insertEventStatement = $this->connection->prepare(<<<SQL
            INSERT INTO event(id, type, count, actor_id, repo_id, payload, create_at, comment)
            VALUES (:id, :type, :count, :actor_id, :repo_id, :payload, :create_at, NULL)
            ON CONFLICT DO NOTHING
SQL);

        $this->connection->transactional(static function() use ($insertEventStatement, $insertRepoStatement, $insertActorStatement, $events): void {
            foreach ($events as $event) {
                $type = EventType::GH_ARCHIVE_MAPPING[$event->type] ?? null;
                if (null === $type) {
                    continue;
                }

                $insertActorStatement->executeStatement([
                    'id' => $event->actor->id,
                    'login' => $event->actor->login,
                    'url' => $event->actor->url,
                    'avatar_url' => $event->actor->avatarUrl,
                ]);

                $insertRepoStatement->executeStatement([
                    'id' => $event->repo->id,
                    'name' => $event->repo->name,
                    'url' => $event->repo->url,
                ]);

                $insertEventStatement->executeStatement([
                    'id' => $event->id,
                    'type' => $type,
                    'count' => $type === EventType::COMMIT ? ($event->payload['size'] ?? 1) : 1,
                    'actor_id' => $event->actor->id,
                    'repo_id' => $event->repo->id,
                    'payload' => json_encode($event->payload, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION),
                    'create_at' => $event->createdAt,
                ]);
            }
        });
    }

    public function update(EventInput $authorInput, int $id): void
    {
        $sql = <<<SQL
        UPDATE event
        SET comment = :comment
        WHERE id = :id
SQL;

        $this->connection->executeQuery($sql, ['id' => $id, 'comment' => $authorInput->comment]);
    }
}
