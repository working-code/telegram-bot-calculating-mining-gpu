<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240510085154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE work ADD overclock_settings JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE work ADD miner_settings JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE work DROP overclock_settings');
        $this->addSql('ALTER TABLE work DROP miner_settings');
    }
}
