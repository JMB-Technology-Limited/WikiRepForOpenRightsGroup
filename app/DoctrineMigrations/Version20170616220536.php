<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170616220536 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE directoki_locale_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE directoki_locale (id INT NOT NULL, project_id INT NOT NULL, creation_event_id INT NOT NULL, public_id VARCHAR(250) NOT NULL, title VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_28CC5C84166D1F9C ON directoki_locale (project_id)');
        $this->addSql('CREATE INDEX IDX_28CC5C84ABB75189 ON directoki_locale (creation_event_id)');
        $this->addSql('CREATE UNIQUE INDEX locale_public_id ON directoki_locale (project_id, public_id)');
        $this->addSql('ALTER TABLE directoki_locale ADD CONSTRAINT FK_28CC5C84166D1F9C FOREIGN KEY (project_id) REFERENCES directoki_project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directoki_locale ADD CONSTRAINT FK_28CC5C84ABB75189 FOREIGN KEY (creation_event_id) REFERENCES directoki_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE directoki_locale_id_seq CASCADE');
        $this->addSql('DROP TABLE directoki_locale');

    }
}
