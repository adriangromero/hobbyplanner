<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Foreign keys — separadas del schema para poder ejecutar el schema sin dependencias.
 */
final class Version20260306000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign key constraints';
    }

    public function up(Schema $schema): void
    {
        // projects → users
        $this->addSql('
            ALTER TABLE projects
                ADD CONSTRAINT fk_projects_user_id
                FOREIGN KEY (user_id) REFERENCES users (id)
                ON DELETE CASCADE
        ');

        // items → projects
        $this->addSql('
            ALTER TABLE items
                ADD CONSTRAINT fk_items_project_id
                FOREIGN KEY (project_id) REFERENCES projects (id)
                ON DELETE CASCADE
        ');

        // items → users
        $this->addSql('
            ALTER TABLE items
                ADD CONSTRAINT fk_items_user_id
                FOREIGN KEY (user_id) REFERENCES users (id)
                ON DELETE CASCADE
        ');

        // work_sessions → users
        $this->addSql('
            ALTER TABLE work_sessions
                ADD CONSTRAINT fk_work_sessions_user_id
                FOREIGN KEY (user_id) REFERENCES users (id)
                ON DELETE CASCADE
        ');

        // work_sessions → projects
        $this->addSql('
            ALTER TABLE work_sessions
                ADD CONSTRAINT fk_work_sessions_project_id
                FOREIGN KEY (project_id) REFERENCES projects (id)
                ON DELETE CASCADE
        ');

        // work_sessions → items
        $this->addSql('
            ALTER TABLE work_sessions
                ADD CONSTRAINT fk_work_sessions_item_id
                FOREIGN KEY (item_id) REFERENCES items (id)
                ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE work_sessions DROP FOREIGN KEY fk_work_sessions_item_id');
        $this->addSql('ALTER TABLE work_sessions DROP FOREIGN KEY fk_work_sessions_project_id');
        $this->addSql('ALTER TABLE work_sessions DROP FOREIGN KEY fk_work_sessions_user_id');
        $this->addSql('ALTER TABLE items DROP FOREIGN KEY fk_items_user_id');
        $this->addSql('ALTER TABLE items DROP FOREIGN KEY fk_items_project_id');
        $this->addSql('ALTER TABLE projects DROP FOREIGN KEY fk_projects_user_id');
    }
}
