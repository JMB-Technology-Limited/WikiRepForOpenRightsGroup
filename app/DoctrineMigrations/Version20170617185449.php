<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170617185449 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE directoki_record_has_field_string_with_locale_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE directoki_record_has_field_string_with_locale_value (id INT NOT NULL, locale_id INT NOT NULL, field_id INT NOT NULL, record_id INT NOT NULL, creation_event_id INT NOT NULL, approval_event_id INT DEFAULT NULL, refusal_event_id INT DEFAULT NULL, value TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_104643DCE559DFD1 ON directoki_record_has_field_string_with_locale_value (locale_id)');
        $this->addSql('CREATE INDEX IDX_104643DC443707B0 ON directoki_record_has_field_string_with_locale_value (field_id)');
        $this->addSql('CREATE INDEX IDX_104643DC4DFD750C ON directoki_record_has_field_string_with_locale_value (record_id)');
        $this->addSql('CREATE INDEX IDX_104643DCABB75189 ON directoki_record_has_field_string_with_locale_value (creation_event_id)');
        $this->addSql('CREATE INDEX IDX_104643DCEEDC4C91 ON directoki_record_has_field_string_with_locale_value (approval_event_id)');
        $this->addSql('CREATE INDEX IDX_104643DCA66B6A08 ON directoki_record_has_field_string_with_locale_value (refusal_event_id)');
        $this->addSql('ALTER TABLE directoki_record_has_field_string_with_locale_value ADD CONSTRAINT FK_104643DCE559DFD1 FOREIGN KEY (locale_id) REFERENCES directoki_locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directoki_record_has_field_string_with_locale_value ADD CONSTRAINT FK_104643DC443707B0 FOREIGN KEY (field_id) REFERENCES directoki_field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directoki_record_has_field_string_with_locale_value ADD CONSTRAINT FK_104643DC4DFD750C FOREIGN KEY (record_id) REFERENCES directoki_record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directoki_record_has_field_string_with_locale_value ADD CONSTRAINT FK_104643DCABB75189 FOREIGN KEY (creation_event_id) REFERENCES directoki_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directoki_record_has_field_string_with_locale_value ADD CONSTRAINT FK_104643DCEEDC4C91 FOREIGN KEY (approval_event_id) REFERENCES directoki_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directoki_record_has_field_string_with_locale_value ADD CONSTRAINT FK_104643DCA66B6A08 FOREIGN KEY (refusal_event_id) REFERENCES directoki_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE directoki_record_has_field_string_with_locale_value_id_seq CASCADE');
        $this->addSql('DROP TABLE directoki_record_has_field_string_with_locale_value');
    }
}
