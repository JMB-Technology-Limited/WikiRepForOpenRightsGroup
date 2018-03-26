<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170716122400 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE directoki_record_locale_cache (record_id INT NOT NULL, locale_id INT NOT NULL, full_text_search TEXT NOT NULL, PRIMARY KEY(record_id, locale_id))');
        $this->addSql('CREATE INDEX IDX_B5F8E0204DFD750C ON directoki_record_locale_cache (record_id)');
        $this->addSql('CREATE INDEX IDX_B5F8E020E559DFD1 ON directoki_record_locale_cache (locale_id)');
        $this->addSql('ALTER TABLE directoki_record_locale_cache ADD CONSTRAINT FK_B5F8E0204DFD750C FOREIGN KEY (record_id) REFERENCES directoki_record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directoki_record_locale_cache ADD CONSTRAINT FK_B5F8E020E559DFD1 FOREIGN KEY (locale_id) REFERENCES directoki_locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE directoki_record_locale_cache');

    }
}
