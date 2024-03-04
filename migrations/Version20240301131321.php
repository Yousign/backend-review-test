<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240301131321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $types = implode("','", [
            '\'CommitCommentEvent',
            'CreateEvent',
            'DeleteEvent',
            'ForkEvent',
            'GollumEvent',
            'IssueCommentEvent',
            'IssuesEvent',
            'MemberEvent',
            'PublicEvent',
            'PullRequestEvent',
            'PullRequestReviewEvent',
            'PullRequestReviewCommentEvent',
            'PullRequestReviewThreadEvent',
            'PushEvent',
            'ReleaseEvent',
            'SponsorshipEvent',
            'WatchEvent\''
        ]);

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor ALTER id TYPE INT');
        $this->addSql('ALTER TABLE event ALTER actor_id TYPE INT');
        $this->addSql('ALTER TABLE event ALTER repo_id TYPE INT');
        $this->addSql('ALTER TABLE event RENAME COLUMN create_at TO created_at');
        $this->addSql('ALTER TABLE event ALTER payload TYPE JSON');
        $this->addSql("ALTER TABLE event ADD CONSTRAINT event_type_check_new CHECK (type IN ($types))");
        $this->addSql('ALTER TABLE event DROP CONSTRAINT event_type_check');
        $this->addSql('ALTER TABLE repo ALTER id TYPE INT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE actor ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE repo ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE "event" ALTER actor_id TYPE BIGINT');
        $this->addSql('ALTER TABLE "event" ALTER repo_id TYPE BIGINT');
        $this->addSql('ALTER TABLE "event" RENAME COLUMN created_at TO create_at');
        $this->addSql('ALTER TABLE event ALTER payload TYPE JSONB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT event_type_check CHECK (type IN (\'COM\', \'MSG\', \'PR\'))');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT event_type_check_new');
    }
}
