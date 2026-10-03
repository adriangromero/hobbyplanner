<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261003000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the number of miniatures in each unit formation row';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE units ADD models_per_row INT NOT NULL DEFAULT 5');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE units DROP COLUMN models_per_row');
    }
}
