<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170914164605 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');


        $this->addSql('ALTER TABLE directoki_project ADD is_web_read_allowed BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('ALTER TABLE directoki_project ADD is_web_moderated_edit_allowed BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('ALTER TABLE directoki_project ADD is_web_report_allowed BOOLEAN DEFAULT \'true\' NOT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE directoki_project DROP is_web_read_allowed');
        $this->addSql('ALTER TABLE directoki_project DROP is_web_moderated_edit_allowed');
        $this->addSql('ALTER TABLE directoki_project DROP is_web_report_allowed');
    }
}
