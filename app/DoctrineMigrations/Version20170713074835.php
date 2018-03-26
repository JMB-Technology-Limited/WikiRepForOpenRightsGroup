<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170713074835 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE directoki_external_check_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE directoki_external_check (id INT NOT NULL, project_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, url TEXT NOT NULL, http_response_code SMALLINT DEFAULT NULL, errorMessage TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_82D70DAD166D1F9C ON directoki_external_check (project_id)');
        $this->addSql('ALTER TABLE directoki_external_check ADD CONSTRAINT FK_82D70DAD166D1F9C FOREIGN KEY (project_id) REFERENCES directoki_project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE directoki_record_report ADD external_check_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE directoki_record_report ADD CONSTRAINT FK_61DDFFA49F9F3714 FOREIGN KEY (external_check_id) REFERENCES directoki_external_check (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_61DDFFA49F9F3714 ON directoki_record_report (external_check_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE directoki_record_report DROP CONSTRAINT FK_61DDFFA49F9F3714');
        $this->addSql('DROP SEQUENCE directoki_external_check_id_seq CASCADE');
        $this->addSql('DROP TABLE directoki_external_check');

        $this->addSql('DROP INDEX IDX_61DDFFA49F9F3714');
        $this->addSql('ALTER TABLE directoki_record_report DROP external_check_id');

    }
}
