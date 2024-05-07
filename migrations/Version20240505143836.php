<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240505143836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE work_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE coin (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, alias VARCHAR(50) NOT NULL, price DOUBLE PRECISION NOT NULL, algorithm VARCHAR(100) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN coin.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN coin.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE work (id INT NOT NULL, gpu_id INT NOT NULL, alias VARCHAR(100) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_534E688098003202 ON work (gpu_id)');
        $this->addSql('COMMENT ON COLUMN work.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN work.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE work ADD CONSTRAINT work__gpu_id__fk FOREIGN KEY (gpu_id) REFERENCES gpu (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('DROP SEQUENCE work_id_seq CASCADE');
        $this->addSql('ALTER TABLE work DROP CONSTRAINT work__gpu_id__fk');
        $this->addSql('DROP TABLE coin');
        $this->addSql('DROP TABLE work');
    }
}
