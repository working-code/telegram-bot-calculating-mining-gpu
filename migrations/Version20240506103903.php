<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240506103903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE work_item (id SERIAL NOT NULL, work_id INT NOT NULL, coin_id INT NOT NULL, alias VARCHAR(100) NOT NULL, hash_rate VARCHAR(100) NOT NULL, count DOUBLE PRECISION NOT NULL, power_consumption INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E6D89426BB3453DB ON work_item (work_id)');
        $this->addSql('CREATE INDEX IDX_E6D8942684BBDA7 ON work_item (coin_id)');
        $this->addSql('COMMENT ON COLUMN work_item.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN work_item.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE work_item ADD CONSTRAINT work_item__work_id__fk FOREIGN KEY (work_id) REFERENCES work (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_item ADD CONSTRAINT work_item__coin_id__fk FOREIGN KEY (coin_id) REFERENCES coin (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE work_item DROP CONSTRAINT work_item__work_id__fk');
        $this->addSql('ALTER TABLE work_item DROP CONSTRAINT work_item__coin_id__fk');
        $this->addSql('DROP TABLE work_item');
    }
}
