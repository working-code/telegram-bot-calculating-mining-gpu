<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240502151820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, telegram_id INT NOT NULL, user_name VARCHAR(100) DEFAULT NULL, first_name VARCHAR(100) DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE rig ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE rig ADD CONSTRAINT rig__user_id__fk FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CFDD4139A76ED395 ON rig (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rig DROP CONSTRAINT rig__user_id__fk');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP INDEX IDX_CFDD4139A76ED395');
        $this->addSql('ALTER TABLE rig DROP user_id');
    }
}
