<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170423070807 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE record RENAME TO directoki_record');
        $this->addSql('ALTER TABLE record_has_field_email_value RENAME TO directoki_record_has_field_email_value');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value RENAME TO directoki_record_has_field_lat_lng_value');
        $this->addSql('ALTER TABLE directory RENAME TO directoki_directory');
        $this->addSql('ALTER TABLE project RENAME TO directoki_project');
        $this->addSql('ALTER TABLE record_has_field_text_value RENAME TO directoki_record_has_field_text_value');
        $this->addSql('ALTER TABLE record_has_field_boolean_value RENAME TO directoki_record_has_field_boolean_value');
        $this->addSql('ALTER TABLE contact RENAME TO directoki_contact');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value RENAME TO directoki_record_has_field_multi_select_value');
        $this->addSql('ALTER TABLE select_value RENAME TO directoki_select_value');
        $this->addSql('ALTER TABLE record_report RENAME TO directoki_record_report');
        $this->addSql('ALTER TABLE record_has_field_string_value RENAME TO directoki_record_has_field_string_value');
        $this->addSql('ALTER TABLE record_note RENAME TO directoki_record_note');
        $this->addSql('ALTER TABLE event RENAME TO directoki_event');
        $this->addSql('ALTER TABLE record_has_field_url_value RENAME TO directoki_record_has_field_url_value');
        $this->addSql('ALTER TABLE field RENAME TO directoki_field');
        $this->addSql('ALTER TABLE record_has_state RENAME TO directoki_record_has_state');

        $this->addSql('ALTER SEQUENCE record_has_field_url_value_id_seq  RENAME TO directoki_record_has_field_url_value_id_seq');
        $this->addSql('ALTER SEQUENCE record_id_seq  RENAME TO directoki_record_id_seq');
        $this->addSql('ALTER SEQUENCE event_id_seq  RENAME TO directoki_event_id_seq');
        $this->addSql('ALTER SEQUENCE field_id_seq  RENAME TO directoki_field_id_seq');
        $this->addSql('ALTER SEQUENCE directory_id_seq  RENAME TO directoki_directory_id_seq');
        $this->addSql('ALTER SEQUENCE record_has_field_boolean_value_id_seq  RENAME TO directoki_record_has_field_boolean_value_id_seq');
        $this->addSql('ALTER SEQUENCE contact_id_seq  RENAME TO directoki_contact_id_seq');
        $this->addSql('ALTER SEQUENCE record_has_field_lat_lng_value_id_seq  RENAME TO directoki_record_has_field_lat_lng_value_id_seq');
        $this->addSql('ALTER SEQUENCE record_has_state_id_seq  RENAME TO directoki_record_has_state_id_seq');
        $this->addSql('ALTER SEQUENCE record_has_field_string_value_id_seq  RENAME TO directoki_record_has_field_string_value_id_seq');
        $this->addSql('ALTER SEQUENCE record_has_field_email_value_id_seq  RENAME TO directoki_record_has_field_email_value_id_seq');
        $this->addSql('ALTER SEQUENCE record_report_id_seq  RENAME TO directoki_record_report_id_seq');
        $this->addSql('ALTER SEQUENCE record_note_id_seq  RENAME TO directoki_record_note_id_seq');
        $this->addSql('ALTER SEQUENCE project_id_seq  RENAME TO directoki_project_id_seq');
        $this->addSql('ALTER SEQUENCE record_has_field_text_value_id_seq  RENAME TO directoki_record_has_field_text_value_id_seq');
        $this->addSql('ALTER SEQUENCE record_has_field_multi_select_value_id_seq  RENAME TO directoki_record_has_field_multi_select_value_id_seq');
        $this->addSql('ALTER SEQUENCE select_value_id_seq  RENAME TO directoki_select_value_id_seq');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');


        $this->addSql('ALTER TABLE directoki_record RENAME TO record');
        $this->addSql('ALTER TABLE directoki_record_has_field_email_value RENAME TO record_has_field_email_value');
        $this->addSql('ALTER TABLE directoki_record_has_field_lat_lng_value RENAME TO record_has_field_lat_lng_value');
        $this->addSql('ALTER TABLE directoki_directory RENAME TO directory');
        $this->addSql('ALTER TABLE directoki_project RENAME TO project');
        $this->addSql('ALTER TABLE directoki_record_has_field_text_value RENAME TO record_has_field_text_value');
        $this->addSql('ALTER TABLE directoki_record_has_field_boolean_value RENAME TO record_has_field_boolean_value');
        $this->addSql('ALTER TABLE directoki_contact RENAME TO contact');
        $this->addSql('ALTER TABLE directoki_record_has_field_multi_select_value RENAME TO record_has_field_multi_select_value');
        $this->addSql('ALTER TABLE directoki_select_value RENAME TO select_value');
        $this->addSql('ALTER TABLE directoki_record_report RENAME TO record_report');
        $this->addSql('ALTER TABLE directoki_record_has_field_string_value RENAME TO record_has_field_string_value');
        $this->addSql('ALTER TABLE directoki_record_note RENAME TO record_note');
        $this->addSql('ALTER TABLE directoki_event RENAME TO event');
        $this->addSql('ALTER TABLE directoki_record_has_field_url_value RENAME TO record_has_field_url_value');
        $this->addSql('ALTER TABLE directoki_field RENAME TO field');
        $this->addSql('ALTER TABLE directoki_record_has_state RENAME TO record_has_state');

        $this->addSql('ALTER SEQUENCE directoki_record_has_field_url_value_id_seq  RENAME TO record_has_field_url_value_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_record_id_seq  RENAME TO record_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_event_id_seq  RENAME TO event_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_field_id_seq  RENAME TO field_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_directory_id_seq  RENAME TO directory_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_record_has_field_boolean_value_id_seq  RENAME TO record_has_field_boolean_value_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_contact_id_seq  RENAME TO contact_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_record_has_field_lat_lng_value_id_seq  RENAME TO record_has_field_lat_lng_value_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_record_has_state_id_seq  RENAME TO record_has_state_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_record_has_field_string_value_id_seq  RENAME TO record_has_field_string_value_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_record_has_field_email_value_id_seq  RENAME TO record_has_field_email_value_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_record_report_id_seq  RENAME TO record_report_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_record_note_id_seq  RENAME TO record_note_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_project_id_seq  RENAME TO project_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_record_has_field_text_value_id_seq  RENAME TO record_has_field_text_value_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_record_has_field_multi_select_value_id_seq  RENAME TO record_has_field_multi_select_value_id_seq');
        $this->addSql('ALTER SEQUENCE directoki_select_value_id_seq  RENAME TO select_value_id_seq');

    }
}
