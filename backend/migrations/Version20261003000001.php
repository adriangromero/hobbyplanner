<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261003000001 extends AbstractMigration
{
    public function getDescription(): string { return 'Add army units, unit components and painting plans alongside hobby projects'; }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE armies (
            id VARCHAR(36) NOT NULL,
            user_id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            PRIMARY KEY (id), INDEX idx_armies_user_id (user_id),
            CONSTRAINT fk_armies_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");

        $this->addSql("CREATE TABLE units (
            id VARCHAR(36) NOT NULL,
            army_id VARCHAR(36) NOT NULL,
            user_id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            PRIMARY KEY (id), INDEX idx_units_army_id (army_id), INDEX idx_units_user_id (user_id),
            CONSTRAINT fk_units_army FOREIGN KEY (army_id) REFERENCES armies (id) ON DELETE CASCADE,
            CONSTRAINT fk_units_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");

        $this->addSql("CREATE TABLE unit_components (
            id VARCHAR(36) NOT NULL,
            unit_id VARCHAR(36) NOT NULL,
            user_id VARCHAR(36) NOT NULL,
            label VARCHAR(255) NOT NULL,
            quantity_total INT NOT NULL,
            quantity_painted INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            PRIMARY KEY (id), INDEX idx_unit_components_unit_id (unit_id), INDEX idx_unit_components_user_id (user_id),
            CONSTRAINT fk_unit_components_unit FOREIGN KEY (unit_id) REFERENCES units (id) ON DELETE CASCADE,
            CONSTRAINT fk_unit_components_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            CONSTRAINT chk_unit_component_total CHECK (quantity_total >= 1),
            CONSTRAINT chk_unit_component_painted CHECK (quantity_painted >= 0 AND quantity_painted <= quantity_total)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");

        $this->addSql("CREATE TABLE painting_plans (
            id VARCHAR(36) NOT NULL,
            unit_id VARCHAR(36) NOT NULL,
            user_id VARCHAR(36) NOT NULL,
            estimated_hours DOUBLE NOT NULL,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            PRIMARY KEY (id), UNIQUE INDEX uniq_painting_plans_unit_id (unit_id), INDEX idx_painting_plans_user_id (user_id),
            CONSTRAINT fk_painting_plans_unit FOREIGN KEY (unit_id) REFERENCES units (id) ON DELETE CASCADE,
            CONSTRAINT fk_painting_plans_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            CONSTRAINT chk_painting_plan_estimate CHECK (estimated_hours > 0)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");

        $this->addSql("CREATE TABLE painting_sessions (
            id VARCHAR(36) NOT NULL,
            painting_plan_id VARCHAR(36) NOT NULL,
            user_id VARCHAR(36) NOT NULL,
            duration_seconds INT NOT NULL,
            worked_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            PRIMARY KEY (id), INDEX idx_painting_sessions_plan_id (painting_plan_id), INDEX idx_painting_sessions_user_id (user_id),
            CONSTRAINT fk_painting_sessions_plan FOREIGN KEY (painting_plan_id) REFERENCES painting_plans (id) ON DELETE CASCADE,
            CONSTRAINT fk_painting_sessions_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            CONSTRAINT chk_painting_session_duration CHECK (duration_seconds > 0)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS painting_sessions');
        $this->addSql('DROP TABLE IF EXISTS painting_plans');
        $this->addSql('DROP TABLE IF EXISTS unit_components');
        $this->addSql('DROP TABLE IF EXISTS units');
        $this->addSql('DROP TABLE IF EXISTS armies');
    }
}
