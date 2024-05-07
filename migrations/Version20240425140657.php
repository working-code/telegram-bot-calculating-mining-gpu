<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240425140657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE gpu (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, brand SMALLINT NOT NULL, alias VARCHAR(30) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE rig (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, electricity_cost DOUBLE PRECISION NOT NULL, power_supply_efficiency INT NOT NULL, motherboard_consumption INT NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE gpu');
        $this->addSql('DROP TABLE rig');
    }
}
