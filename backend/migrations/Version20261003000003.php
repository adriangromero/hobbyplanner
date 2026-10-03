<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261003000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unit categories and persistent manual ordering';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE units ADD category VARCHAR(20) NOT NULL DEFAULT 'infantry'");
        $this->addSql('ALTER TABLE units ADD sort_order INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE units DROP COLUMN sort_order');
        $this->addSql('ALTER TABLE units DROP COLUMN category');
    }
}
