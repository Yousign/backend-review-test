<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250726200653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $types = [
            'COM',
            'MSG',
            'PullRequestEvent',
         'IssueCommentEvent',
         'PushEvent',
         'WatchEvent',
         'CommitCommentEvent',
         'CreateEvent',
         'DeleteEvent',
         'ForkEvent',
         'GollumEvent',
         'IssuesEvent',
         'MemberEvent',
         'PublicEvent',
         'PullRequestReviewEvent',
         'PullRequestReviewCommentEvent',
         'PullRequestReviewThreadEvent',
         'ReleaseEvent',
         'SponsorshipEvent'
        ];
        $this->addSql('
            ALTER TABLE "event" 
            DROP CONSTRAINT IF EXISTS event_type_check,
            ADD CONSTRAINT event_type_check 
            CHECK (type IN (\'' . implode('\', \'', $types) . '\'));
            ');

    }

    public function down(Schema $schema): void
    {

        $this->addSql('
            ALTER TABLE "event" 
            DROP CONSTRAINT IF EXISTS event_type_check,
            ADD CONSTRAINT event_type_check 
            CHECK (type IN (\'COM\', \'MSG\', \'PR\'));
            ');
    }
}
