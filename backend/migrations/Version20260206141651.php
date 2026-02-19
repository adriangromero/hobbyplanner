<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206141651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE items (project_id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, estimated_hours DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, id VARCHAR(36) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE work_sessions (user_id VARCHAR(36) NOT NULL, project_id VARCHAR(36) NOT NULL, item_id VARCHAR(36) NOT NULL, hours DOUBLE PRECISION NOT NULL, worked_at DATETIME NOT NULL, created_at DATETIME NOT NULL, id VARCHAR(36) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE items');
        $this->addSql('DROP TABLE work_sessions');
    }
}
