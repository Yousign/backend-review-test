<?php


namespace App\Infrastructure\Event\Doctrine\Repository;

use App\Application\Dto\EventInput;
use App\Domain\Entity\Actor;
use App\Domain\Entity\Event;
use App\Domain\Entity\Repo;
use App\Domain\Repository\WriteEventRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class DbalWriteEventRepository implements WriteEventRepositoryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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

    public function batchInsert(Event $event): void
    {
        $this->insertActor($event->actor());
        $this->insertRepo($event->repo());

        try {
            $this->connection->insert('event', [
                'id' => $event->id(),
                'type' => $event->type(),
                'count' => $event->count(),
                'actor_id' => $event->actor()->id(),
                'repo_id' => $event->repo()->id(),
                'payload' => json_encode($event->payload()),
                'create_at' => $event->createAt()->format('Y-m-d H:i:s'),
                'comment' => $event->comment(),
            ]);
            unset($actor, $repo);
        } catch (UniqueConstraintViolationException) {
            // no-op
        } catch (Exception $e) {
        }
    }

    private function insertActor(Actor $actor): void
    {
        try {
            $this->connection->insert('actor', [
                'id' => $actor->id(),
                'login' => $actor->login(),
                'url' => $actor->url(),
                'avatar_url' => $actor->avatarUrl(),
            ]);
        } catch (UniqueConstraintViolationException) {
        }
    }

    private function insertRepo(Repo $repo): void
    {
        try {
            $this->connection->insert('repo', [
                'id' => $repo->id(),
                'name' => $repo->name(),
                'url' => $repo->url(),
            ]);
        } catch (UniqueConstraintViolationException) {
        }
    }
}
