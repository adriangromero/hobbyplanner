<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225094603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE items DROP FOREIGN KEY `fk_item_project`');
        $this->addSql('ALTER TABLE items DROP FOREIGN KEY `fk_item_user`');
        $this->addSql('DROP INDEX idx_item_project ON items');
        $this->addSql('DROP INDEX idx_item_user ON items');
        $this->addSql('ALTER TABLE items CHANGE id id CHAR(36) NOT NULL, CHANGE estimated_hours estimated_hours DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE projects DROP FOREIGN KEY `fk_project_user`');
        $this->addSql('DROP INDEX idx_project_user ON projects');
        $this->addSql('ALTER TABLE projects DROP user_id, CHANGE id id CHAR(36) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE users DROP active, CHANGE id id CHAR(36) NOT NULL, CHANGE email email CHAR(36) NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE users RENAME INDEX email TO UNIQ_1483A5E9E7927C74');
        $this->addSql('ALTER TABLE work_sessions DROP FOREIGN KEY `fk_session_item`');
        $this->addSql('ALTER TABLE work_sessions DROP FOREIGN KEY `fk_session_project`');
        $this->addSql('ALTER TABLE work_sessions DROP FOREIGN KEY `fk_session_user`');
        $this->addSql('DROP INDEX idx_session_active ON work_sessions');
        $this->addSql('ALTER TABLE work_sessions CHANGE id id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE work_sessions RENAME INDEX idx_session_user TO idx_user_id');
        $this->addSql('ALTER TABLE work_sessions RENAME INDEX idx_session_project TO idx_project_id');
        $this->addSql('ALTER TABLE work_sessions RENAME INDEX idx_session_item TO idx_item_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE items CHANGE estimated_hours estimated_hours NUMERIC(6, 2) NOT NULL, CHANGE id id CHAR(36) DEFAULT \'uuid()\' NOT NULL');
        $this->addSql('ALTER TABLE items ADD CONSTRAINT `fk_item_project` FOREIGN KEY (project_id) REFERENCES projects (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE items ADD CONSTRAINT `fk_item_user` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_item_project ON items (project_id)');
        $this->addSql('CREATE INDEX idx_item_user ON items (user_id)');
        $this->addSql('ALTER TABLE projects ADD user_id CHAR(36) NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE id id CHAR(36) DEFAULT \'uuid()\' NOT NULL');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT `fk_project_user` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_project_user ON projects (user_id)');
        $this->addSql('ALTER TABLE users ADD active TINYINT DEFAULT 1 NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE id id CHAR(36) DEFAULT \'uuid()\' NOT NULL');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9e7927c74 TO email');
        $this->addSql('ALTER TABLE work_sessions CHANGE id id CHAR(36) DEFAULT \'uuid()\' NOT NULL');
        $this->addSql('ALTER TABLE work_sessions ADD CONSTRAINT `fk_session_item` FOREIGN KEY (item_id) REFERENCES items (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE work_sessions ADD CONSTRAINT `fk_session_project` FOREIGN KEY (project_id) REFERENCES projects (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE work_sessions ADD CONSTRAINT `fk_session_user` FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_session_active ON work_sessions (ended_at)');
        $this->addSql('ALTER TABLE work_sessions RENAME INDEX idx_project_id TO idx_session_project');
        $this->addSql('ALTER TABLE work_sessions RENAME INDEX idx_item_id TO idx_session_item');
        $this->addSql('ALTER TABLE work_sessions RENAME INDEX idx_user_id TO idx_session_user');
    }
}
