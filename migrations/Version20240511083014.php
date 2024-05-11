<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240511083014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_494dd4898003202');
        $this->addSql('CREATE INDEX rig_item__gpu_id__ind ON rig_item (gpu_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX rig_item__gpu_id__ind');
        $this->addSql('CREATE UNIQUE INDEX uniq_494dd4898003202 ON rig_item (gpu_id)');
    }
}
