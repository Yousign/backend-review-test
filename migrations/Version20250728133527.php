<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250728133527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event RENAME COLUMN create_at TO created_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "event" RENAME COLUMN created_at TO create_at');
    }
}
