<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261003000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make projects the single root for general hobby work and army inventory';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE projects ADD project_type VARCHAR(20) NOT NULL DEFAULT 'general' COMMENT '(DC2Type:project_type)'");
        $this->addSql("INSERT INTO projects (id, user_id, name, description, status, project_type, created_at, updated_at)
            SELECT id, user_id, name, '', 'active', 'army', created_at, updated_at FROM armies");

        $this->addSql('ALTER TABLE units DROP FOREIGN KEY fk_units_army');
        $this->addSql('DROP INDEX idx_units_army_id ON units');
        $this->addSql('ALTER TABLE units CHANGE army_id project_id VARCHAR(36) NOT NULL');
        $this->addSql('CREATE INDEX idx_units_project_id ON units (project_id)');
        $this->addSql('ALTER TABLE units ADD CONSTRAINT fk_units_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE armies');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            (int) $this->connection->fetchOne("SELECT COUNT(*) FROM projects WHERE project_type = 'army' AND (description <> '' OR status <> 'active')") > 0,
            'Army projects now contain project details that cannot be represented by the old Army table.',
        );
        $this->abortIf(
            (int) $this->connection->fetchOne("SELECT COUNT(*) FROM items i INNER JOIN projects p ON p.id = i.project_id WHERE p.project_type = 'army'") > 0,
            'Army projects contain generic project tasks; move or remove them before reverting this migration.',
        );

        $this->addSql("CREATE TABLE armies (
            id VARCHAR(36) NOT NULL,
            user_id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            PRIMARY KEY (id), INDEX idx_armies_user_id (user_id),
            CONSTRAINT fk_armies_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
        $this->addSql("INSERT INTO armies (id, user_id, name, created_at, updated_at)
            SELECT id, user_id, name, created_at, updated_at FROM projects WHERE project_type = 'army'");

        $this->addSql('ALTER TABLE units DROP FOREIGN KEY fk_units_project');
        $this->addSql('DROP INDEX idx_units_project_id ON units');
        $this->addSql('ALTER TABLE units CHANGE project_id army_id VARCHAR(36) NOT NULL');
        $this->addSql('CREATE INDEX idx_units_army_id ON units (army_id)');
        $this->addSql('ALTER TABLE units ADD CONSTRAINT fk_units_army FOREIGN KEY (army_id) REFERENCES armies (id) ON DELETE CASCADE');
        $this->addSql("DELETE FROM projects WHERE project_type = 'army'");
        $this->addSql('ALTER TABLE projects DROP project_type');
    }
}
