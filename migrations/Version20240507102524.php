<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240507102524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX coin__alias__unq ON coin (alias)');
        $this->addSql('CREATE UNIQUE INDEX gpu__alias__unq ON gpu (alias)');
        $this->addSql('ALTER INDEX idx_cfdd4139a76ed395 RENAME TO rig__user_id__ind');
        $this->addSql('ALTER INDEX idx_494dd485cfbcbcd RENAME TO rig_item__rig_id__ind');
        $this->addSql('ALTER INDEX idx_534e688098003202 RENAME TO work__gpu_id__ind');
        $this->addSql('ALTER INDEX idx_e6d89426bb3453db RENAME TO work_item__work_id__ind');
        $this->addSql('ALTER INDEX idx_e6d8942684bbda7 RENAME TO work_item__coin_id__ind');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER INDEX work_item__coin_id__ind RENAME TO idx_e6d8942684bbda7');
        $this->addSql('ALTER INDEX work_item__work_id__ind RENAME TO idx_e6d89426bb3453db');
        $this->addSql('DROP INDEX gpu__alias__unq');
        $this->addSql('ALTER INDEX rig_item__rig_id__ind RENAME TO idx_494dd485cfbcbcd');
        $this->addSql('ALTER INDEX rig__user_id__ind RENAME TO idx_cfdd4139a76ed395');
        $this->addSql('DROP INDEX coin__alias__unq');
        $this->addSql('ALTER INDEX work__gpu_id__ind RENAME TO idx_534e688098003202');
    }
}
