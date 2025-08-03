<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250803210526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('COMMENT ON COLUMN "event".type IS \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('COMMENT ON COLUMN "event".type IS \'(DC2Type:EventType)\'');
    }
}
