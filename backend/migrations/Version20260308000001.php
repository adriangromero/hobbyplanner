<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create full schema: users, projects, items, work_sessions with FK and indices';
    }

    public function up(Schema $schema): void
    {
        // ── Users ────────────────────────────────────────────
        $this->addSql('
            CREATE TABLE users (
                id         VARCHAR(36)  NOT NULL,
                email      VARCHAR(255) NOT NULL,
                password   VARCHAR(255) NOT NULL,
                name       VARCHAR(255) NOT NULL,
                roles      JSON         NOT NULL,
                active     TINYINT(1)   NOT NULL DEFAULT 1,
                created_at DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                updated_at DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                PRIMARY KEY (id),
                UNIQUE INDEX uniq_users_email (email)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');

        // ── Projects ─────────────────────────────────────────
        $this->addSql('
            CREATE TABLE projects (
                id          VARCHAR(36)  NOT NULL,
                user_id     VARCHAR(36)  NOT NULL,
                name        VARCHAR(255) NOT NULL,
                description TEXT         NOT NULL,
                status      VARCHAR(20)  NOT NULL DEFAULT \'active\',
                created_at  DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                updated_at  DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                PRIMARY KEY (id),
                INDEX idx_projects_user_id (user_id),
                CONSTRAINT fk_projects_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');

        // ── Items ────────────────────────────────────────────
        $this->addSql('
            CREATE TABLE items (
                id              VARCHAR(36)  NOT NULL,
                project_id      VARCHAR(36)  NOT NULL,
                user_id         VARCHAR(36)  NOT NULL,
                name            VARCHAR(255) NOT NULL,
                estimated_hours DOUBLE       NOT NULL,
                status          VARCHAR(20)  NOT NULL DEFAULT \'pending\',
                created_at      DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                updated_at      DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                PRIMARY KEY (id),
                INDEX idx_items_project_id (project_id),
                INDEX idx_items_user_id (user_id),
                INDEX idx_items_status (status),
                CONSTRAINT fk_items_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
                CONSTRAINT fk_items_user    FOREIGN KEY (user_id)    REFERENCES users (id)    ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');

        // ── Work Sessions ────────────────────────────────────
        $this->addSql('
            CREATE TABLE work_sessions (
                id         VARCHAR(36) NOT NULL,
                project_id VARCHAR(36) NOT NULL,
                item_id    VARCHAR(36) NOT NULL,
                user_id    VARCHAR(36) NOT NULL,
                started_at DATETIME    NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                ended_at   DATETIME    DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                PRIMARY KEY (id),
                INDEX idx_work_sessions_project_id (project_id),
                INDEX idx_work_sessions_item_id (item_id),
                INDEX idx_work_sessions_user_id (user_id),
                CONSTRAINT fk_work_sessions_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
                CONSTRAINT fk_work_sessions_item    FOREIGN KEY (item_id)    REFERENCES items (id)    ON DELETE CASCADE,
                CONSTRAINT fk_work_sessions_user    FOREIGN KEY (user_id)    REFERENCES users (id)    ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS work_sessions');
        $this->addSql('DROP TABLE IF EXISTS items');
        $this->addSql('DROP TABLE IF EXISTS projects');
        $this->addSql('DROP TABLE IF EXISTS users');
    }
}
