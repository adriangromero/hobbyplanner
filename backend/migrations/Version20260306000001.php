<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Schema inicial — tablas, columnas e índices.
 */
final class Version20260306000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tables: users, projects, items, work_sessions';
    }

    public function up(Schema $schema): void
    {
        // ── users ───────────────────────────────────────────
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
                UNIQUE INDEX uniq_users_email (email),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');

        // ── projects ───────────────────────────────────────
        $this->addSql('
            CREATE TABLE projects (
                id          VARCHAR(36)  NOT NULL,
                user_id     VARCHAR(36)  NOT NULL,
                name        VARCHAR(255) NOT NULL,
                description LONGTEXT     NOT NULL,
                created_at  DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                updated_at  DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                INDEX idx_projects_user_id (user_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');

        // ── items ──────────────────────────────────────────
        $this->addSql('
            CREATE TABLE items (
                id              VARCHAR(36)  NOT NULL,
                project_id      VARCHAR(36)  NOT NULL,
                user_id         VARCHAR(36)  NOT NULL,
                name            VARCHAR(255) NOT NULL,
                estimated_hours DOUBLE PRECISION NOT NULL,
                created_at      DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                updated_at      DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                INDEX idx_items_project_id (project_id),
                INDEX idx_items_user_id (user_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');

        // ── work_sessions ──────────────────────────────────
        $this->addSql('
            CREATE TABLE work_sessions (
                id         VARCHAR(36) NOT NULL,
                user_id    VARCHAR(36) NOT NULL,
                project_id VARCHAR(36) NOT NULL,
                item_id    VARCHAR(36) NOT NULL,
                started_at DATETIME    NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                ended_at   DATETIME    DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                INDEX idx_user_id (user_id),
                INDEX idx_project_id (project_id),
                INDEX idx_item_id (item_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE work_sessions');
        $this->addSql('DROP TABLE items');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE users');
    }
}
