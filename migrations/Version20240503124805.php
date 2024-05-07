<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240503124805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rig_item (id SERIAL NOT NULL, rig_id INT NOT NULL, gpu_id INT NOT NULL, count INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_494DD485CFBCBCD ON rig_item (rig_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_494DD4898003202 ON rig_item (gpu_id)');
        $this->addSql('COMMENT ON COLUMN rig_item.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN rig_item.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE rig_item ADD CONSTRAINT rig_item__rig_id__fk FOREIGN KEY (rig_id) REFERENCES rig (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rig_item ADD CONSTRAINT rig_item__gpu_id__fk FOREIGN KEY (gpu_id) REFERENCES gpu (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rig_item DROP CONSTRAINT rig_item__rig_id__fk');
        $this->addSql('ALTER TABLE rig_item DROP CONSTRAINT rig_item__gpu_id__fk');
        $this->addSql('DROP TABLE rig_item');
    }
}
