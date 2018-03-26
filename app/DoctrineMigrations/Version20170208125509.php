<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170208125509 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE record_has_field_url_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE record_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE event_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE field_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE directory_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE record_has_field_boolean_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE contact_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE record_has_field_lat_lng_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE record_has_state_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE record_has_field_string_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE record_has_field_email_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE record_report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE record_note_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE record_has_field_text_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE record_has_field_url_value (id INT NOT NULL, field_id INT NOT NULL, record_id INT NOT NULL, creation_event_id INT NOT NULL, approval_event_id INT DEFAULT NULL, refusal_event_id INT DEFAULT NULL, value TEXT NOT NULL, locale VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_13D8416A443707B0 ON record_has_field_url_value (field_id)');
        $this->addSql('CREATE INDEX IDX_13D8416A4DFD750C ON record_has_field_url_value (record_id)');
        $this->addSql('CREATE INDEX IDX_13D8416AABB75189 ON record_has_field_url_value (creation_event_id)');
        $this->addSql('CREATE INDEX IDX_13D8416AEEDC4C91 ON record_has_field_url_value (approval_event_id)');
        $this->addSql('CREATE INDEX IDX_13D8416AA66B6A08 ON record_has_field_url_value (refusal_event_id)');
        $this->addSql('CREATE TABLE record (id INT NOT NULL, directory_id INT NOT NULL, creation_event_id INT NOT NULL, public_id VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cached_state TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9B349F912C94069F ON record (directory_id)');
        $this->addSql('CREATE INDEX IDX_9B349F91ABB75189 ON record (creation_event_id)');
        $this->addSql('CREATE UNIQUE INDEX record_public_id ON record (directory_id, public_id)');
        $this->addSql('CREATE TABLE event (id INT NOT NULL, project_id INT NOT NULL, contact_id INT DEFAULT NULL, user_id INT DEFAULT NULL, comment TEXT DEFAULT NULL, api_version SMALLINT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ip VARCHAR(250) DEFAULT NULL, user_agent TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7166D1F9C ON event (project_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7E7A1254A ON event (contact_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7A76ED395 ON event (user_id)');
        $this->addSql('CREATE TABLE user_account (id INT NOT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locked BOOLEAN NOT NULL, expired BOOLEAN NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, roles TEXT NOT NULL, credentials_expired BOOLEAN NOT NULL, credentials_expire_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_253B48AE92FC23A8 ON user_account (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_253B48AEA0D96FBF ON user_account (email_canonical)');
        $this->addSql('COMMENT ON COLUMN user_account.roles IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE field (id INT NOT NULL, directory_id INT NOT NULL, creation_event_id INT NOT NULL, public_id VARCHAR(250) NOT NULL, title VARCHAR(250) NOT NULL, sort SMALLINT NOT NULL, field_type VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5BF545582C94069F ON field (directory_id)');
        $this->addSql('CREATE INDEX IDX_5BF54558ABB75189 ON field (creation_event_id)');
        $this->addSql('CREATE UNIQUE INDEX field_public_id ON field (directory_id, public_id)');
        $this->addSql('CREATE TABLE directory (id INT NOT NULL, project_id INT NOT NULL, creation_event_id INT NOT NULL, public_id VARCHAR(250) NOT NULL, title_singular VARCHAR(250) NOT NULL, title_plural VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_467844DA166D1F9C ON directory (project_id)');
        $this->addSql('CREATE INDEX IDX_467844DAABB75189 ON directory (creation_event_id)');
        $this->addSql('CREATE UNIQUE INDEX directory_public_id ON directory (project_id, public_id)');
        $this->addSql('CREATE TABLE record_has_field_boolean_value (id INT NOT NULL, field_id INT NOT NULL, record_id INT NOT NULL, creation_event_id INT NOT NULL, approval_event_id INT DEFAULT NULL, refusal_event_id INT DEFAULT NULL, value BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A65F2C36443707B0 ON record_has_field_boolean_value (field_id)');
        $this->addSql('CREATE INDEX IDX_A65F2C364DFD750C ON record_has_field_boolean_value (record_id)');
        $this->addSql('CREATE INDEX IDX_A65F2C36ABB75189 ON record_has_field_boolean_value (creation_event_id)');
        $this->addSql('CREATE INDEX IDX_A65F2C36EEDC4C91 ON record_has_field_boolean_value (approval_event_id)');
        $this->addSql('CREATE INDEX IDX_A65F2C36A66B6A08 ON record_has_field_boolean_value (refusal_event_id)');
        $this->addSql('CREATE TABLE contact (id INT NOT NULL, project_id INT NOT NULL, public_id VARCHAR(250) NOT NULL, email VARCHAR(250) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4C62E638166D1F9C ON contact (project_id)');
        $this->addSql('CREATE UNIQUE INDEX contact_public_id ON contact (project_id, public_id)');
        $this->addSql('CREATE TABLE record_has_field_lat_lng_value (id INT NOT NULL, field_id INT NOT NULL, record_id INT NOT NULL, creation_event_id INT NOT NULL, approval_event_id INT DEFAULT NULL, refusal_event_id INT DEFAULT NULL, lat DOUBLE PRECISION DEFAULT NULL, lng DOUBLE PRECISION DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5B806808443707B0 ON record_has_field_lat_lng_value (field_id)');
        $this->addSql('CREATE INDEX IDX_5B8068084DFD750C ON record_has_field_lat_lng_value (record_id)');
        $this->addSql('CREATE INDEX IDX_5B806808ABB75189 ON record_has_field_lat_lng_value (creation_event_id)');
        $this->addSql('CREATE INDEX IDX_5B806808EEDC4C91 ON record_has_field_lat_lng_value (approval_event_id)');
        $this->addSql('CREATE INDEX IDX_5B806808A66B6A08 ON record_has_field_lat_lng_value (refusal_event_id)');
        $this->addSql('CREATE TABLE record_has_state (id INT NOT NULL, record_id INT NOT NULL, creation_event_id INT NOT NULL, approval_event_id INT DEFAULT NULL, refusal_event_id INT DEFAULT NULL, state TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C28B72214DFD750C ON record_has_state (record_id)');
        $this->addSql('CREATE INDEX IDX_C28B7221ABB75189 ON record_has_state (creation_event_id)');
        $this->addSql('CREATE INDEX IDX_C28B7221EEDC4C91 ON record_has_state (approval_event_id)');
        $this->addSql('CREATE INDEX IDX_C28B7221A66B6A08 ON record_has_state (refusal_event_id)');
        $this->addSql('CREATE TABLE record_has_field_string_value (id INT NOT NULL, field_id INT NOT NULL, record_id INT NOT NULL, creation_event_id INT NOT NULL, approval_event_id INT DEFAULT NULL, refusal_event_id INT DEFAULT NULL, value TEXT NOT NULL, locale VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_105079F5443707B0 ON record_has_field_string_value (field_id)');
        $this->addSql('CREATE INDEX IDX_105079F54DFD750C ON record_has_field_string_value (record_id)');
        $this->addSql('CREATE INDEX IDX_105079F5ABB75189 ON record_has_field_string_value (creation_event_id)');
        $this->addSql('CREATE INDEX IDX_105079F5EEDC4C91 ON record_has_field_string_value (approval_event_id)');
        $this->addSql('CREATE INDEX IDX_105079F5A66B6A08 ON record_has_field_string_value (refusal_event_id)');
        $this->addSql('CREATE TABLE record_has_field_email_value (id INT NOT NULL, field_id INT NOT NULL, record_id INT NOT NULL, creation_event_id INT NOT NULL, approval_event_id INT DEFAULT NULL, refusal_event_id INT DEFAULT NULL, value TEXT NOT NULL, locale VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7D1A9952443707B0 ON record_has_field_email_value (field_id)');
        $this->addSql('CREATE INDEX IDX_7D1A99524DFD750C ON record_has_field_email_value (record_id)');
        $this->addSql('CREATE INDEX IDX_7D1A9952ABB75189 ON record_has_field_email_value (creation_event_id)');
        $this->addSql('CREATE INDEX IDX_7D1A9952EEDC4C91 ON record_has_field_email_value (approval_event_id)');
        $this->addSql('CREATE INDEX IDX_7D1A9952A66B6A08 ON record_has_field_email_value (refusal_event_id)');
        $this->addSql('CREATE TABLE record_report (id INT NOT NULL, record_id INT NOT NULL, creation_event_id INT NOT NULL, resolution_event_id INT DEFAULT NULL, description TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, resolved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A67331FA4DFD750C ON record_report (record_id)');
        $this->addSql('CREATE INDEX IDX_A67331FAABB75189 ON record_report (creation_event_id)');
        $this->addSql('CREATE INDEX IDX_A67331FA464B2689 ON record_report (resolution_event_id)');
        $this->addSql('CREATE TABLE record_note (id INT NOT NULL, record_id INT NOT NULL, created_by INT NOT NULL, note TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2A0A6BC04DFD750C ON record_note (record_id)');
        $this->addSql('CREATE INDEX IDX_2A0A6BC0DE12AB56 ON record_note (created_by)');
        $this->addSql('CREATE TABLE project (id INT NOT NULL, owner_id INT NOT NULL, public_id VARCHAR(250) NOT NULL, title VARCHAR(250) NOT NULL, is_api_read_allowed BOOLEAN NOT NULL, is_api_moderated_edit_allowed BOOLEAN NOT NULL, is_api_report_allowed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EEB5B48B91 ON project (public_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE7E3C61F9 ON project (owner_id)');
        $this->addSql('CREATE TABLE record_has_field_text_value (id INT NOT NULL, field_id INT NOT NULL, record_id INT NOT NULL, creation_event_id INT NOT NULL, approval_event_id INT DEFAULT NULL, refusal_event_id INT DEFAULT NULL, value TEXT NOT NULL, locale VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9BA7173443707B0 ON record_has_field_text_value (field_id)');
        $this->addSql('CREATE INDEX IDX_9BA71734DFD750C ON record_has_field_text_value (record_id)');
        $this->addSql('CREATE INDEX IDX_9BA7173ABB75189 ON record_has_field_text_value (creation_event_id)');
        $this->addSql('CREATE INDEX IDX_9BA7173EEDC4C91 ON record_has_field_text_value (approval_event_id)');
        $this->addSql('CREATE INDEX IDX_9BA7173A66B6A08 ON record_has_field_text_value (refusal_event_id)');
        $this->addSql('ALTER TABLE record_has_field_url_value ADD CONSTRAINT FK_13D8416A443707B0 FOREIGN KEY (field_id) REFERENCES field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_url_value ADD CONSTRAINT FK_13D8416A4DFD750C FOREIGN KEY (record_id) REFERENCES record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_url_value ADD CONSTRAINT FK_13D8416AABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_url_value ADD CONSTRAINT FK_13D8416AEEDC4C91 FOREIGN KEY (approval_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_url_value ADD CONSTRAINT FK_13D8416AA66B6A08 FOREIGN KEY (refusal_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F912C94069F FOREIGN KEY (directory_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F91ABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE field ADD CONSTRAINT FK_5BF545582C94069F FOREIGN KEY (directory_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE field ADD CONSTRAINT FK_5BF54558ABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_467844DA166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_467844DAABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_boolean_value ADD CONSTRAINT FK_A65F2C36443707B0 FOREIGN KEY (field_id) REFERENCES field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_boolean_value ADD CONSTRAINT FK_A65F2C364DFD750C FOREIGN KEY (record_id) REFERENCES record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_boolean_value ADD CONSTRAINT FK_A65F2C36ABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_boolean_value ADD CONSTRAINT FK_A65F2C36EEDC4C91 FOREIGN KEY (approval_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_boolean_value ADD CONSTRAINT FK_A65F2C36A66B6A08 FOREIGN KEY (refusal_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value ADD CONSTRAINT FK_5B806808443707B0 FOREIGN KEY (field_id) REFERENCES field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value ADD CONSTRAINT FK_5B8068084DFD750C FOREIGN KEY (record_id) REFERENCES record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value ADD CONSTRAINT FK_5B806808ABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value ADD CONSTRAINT FK_5B806808EEDC4C91 FOREIGN KEY (approval_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value ADD CONSTRAINT FK_5B806808A66B6A08 FOREIGN KEY (refusal_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_state ADD CONSTRAINT FK_C28B72214DFD750C FOREIGN KEY (record_id) REFERENCES record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_state ADD CONSTRAINT FK_C28B7221ABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_state ADD CONSTRAINT FK_C28B7221EEDC4C91 FOREIGN KEY (approval_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_state ADD CONSTRAINT FK_C28B7221A66B6A08 FOREIGN KEY (refusal_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_string_value ADD CONSTRAINT FK_105079F5443707B0 FOREIGN KEY (field_id) REFERENCES field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_string_value ADD CONSTRAINT FK_105079F54DFD750C FOREIGN KEY (record_id) REFERENCES record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_string_value ADD CONSTRAINT FK_105079F5ABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_string_value ADD CONSTRAINT FK_105079F5EEDC4C91 FOREIGN KEY (approval_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_string_value ADD CONSTRAINT FK_105079F5A66B6A08 FOREIGN KEY (refusal_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_email_value ADD CONSTRAINT FK_7D1A9952443707B0 FOREIGN KEY (field_id) REFERENCES field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_email_value ADD CONSTRAINT FK_7D1A99524DFD750C FOREIGN KEY (record_id) REFERENCES record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_email_value ADD CONSTRAINT FK_7D1A9952ABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_email_value ADD CONSTRAINT FK_7D1A9952EEDC4C91 FOREIGN KEY (approval_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_email_value ADD CONSTRAINT FK_7D1A9952A66B6A08 FOREIGN KEY (refusal_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_report ADD CONSTRAINT FK_A67331FA4DFD750C FOREIGN KEY (record_id) REFERENCES record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_report ADD CONSTRAINT FK_A67331FAABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_report ADD CONSTRAINT FK_A67331FA464B2689 FOREIGN KEY (resolution_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_note ADD CONSTRAINT FK_2A0A6BC04DFD750C FOREIGN KEY (record_id) REFERENCES record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_note ADD CONSTRAINT FK_2A0A6BC0DE12AB56 FOREIGN KEY (created_by) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_text_value ADD CONSTRAINT FK_9BA7173443707B0 FOREIGN KEY (field_id) REFERENCES field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_text_value ADD CONSTRAINT FK_9BA71734DFD750C FOREIGN KEY (record_id) REFERENCES record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_text_value ADD CONSTRAINT FK_9BA7173ABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_text_value ADD CONSTRAINT FK_9BA7173EEDC4C91 FOREIGN KEY (approval_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_text_value ADD CONSTRAINT FK_9BA7173A66B6A08 FOREIGN KEY (refusal_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE record_has_field_url_value DROP CONSTRAINT FK_13D8416A4DFD750C');
        $this->addSql('ALTER TABLE record_has_field_boolean_value DROP CONSTRAINT FK_A65F2C364DFD750C');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value DROP CONSTRAINT FK_5B8068084DFD750C');
        $this->addSql('ALTER TABLE record_has_state DROP CONSTRAINT FK_C28B72214DFD750C');
        $this->addSql('ALTER TABLE record_has_field_string_value DROP CONSTRAINT FK_105079F54DFD750C');
        $this->addSql('ALTER TABLE record_has_field_email_value DROP CONSTRAINT FK_7D1A99524DFD750C');
        $this->addSql('ALTER TABLE record_report DROP CONSTRAINT FK_A67331FA4DFD750C');
        $this->addSql('ALTER TABLE record_note DROP CONSTRAINT FK_2A0A6BC04DFD750C');
        $this->addSql('ALTER TABLE record_has_field_text_value DROP CONSTRAINT FK_9BA71734DFD750C');
        $this->addSql('ALTER TABLE record_has_field_url_value DROP CONSTRAINT FK_13D8416AABB75189');
        $this->addSql('ALTER TABLE record_has_field_url_value DROP CONSTRAINT FK_13D8416AEEDC4C91');
        $this->addSql('ALTER TABLE record_has_field_url_value DROP CONSTRAINT FK_13D8416AA66B6A08');
        $this->addSql('ALTER TABLE record DROP CONSTRAINT FK_9B349F91ABB75189');
        $this->addSql('ALTER TABLE field DROP CONSTRAINT FK_5BF54558ABB75189');
        $this->addSql('ALTER TABLE directory DROP CONSTRAINT FK_467844DAABB75189');
        $this->addSql('ALTER TABLE record_has_field_boolean_value DROP CONSTRAINT FK_A65F2C36ABB75189');
        $this->addSql('ALTER TABLE record_has_field_boolean_value DROP CONSTRAINT FK_A65F2C36EEDC4C91');
        $this->addSql('ALTER TABLE record_has_field_boolean_value DROP CONSTRAINT FK_A65F2C36A66B6A08');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value DROP CONSTRAINT FK_5B806808ABB75189');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value DROP CONSTRAINT FK_5B806808EEDC4C91');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value DROP CONSTRAINT FK_5B806808A66B6A08');
        $this->addSql('ALTER TABLE record_has_state DROP CONSTRAINT FK_C28B7221ABB75189');
        $this->addSql('ALTER TABLE record_has_state DROP CONSTRAINT FK_C28B7221EEDC4C91');
        $this->addSql('ALTER TABLE record_has_state DROP CONSTRAINT FK_C28B7221A66B6A08');
        $this->addSql('ALTER TABLE record_has_field_string_value DROP CONSTRAINT FK_105079F5ABB75189');
        $this->addSql('ALTER TABLE record_has_field_string_value DROP CONSTRAINT FK_105079F5EEDC4C91');
        $this->addSql('ALTER TABLE record_has_field_string_value DROP CONSTRAINT FK_105079F5A66B6A08');
        $this->addSql('ALTER TABLE record_has_field_email_value DROP CONSTRAINT FK_7D1A9952ABB75189');
        $this->addSql('ALTER TABLE record_has_field_email_value DROP CONSTRAINT FK_7D1A9952EEDC4C91');
        $this->addSql('ALTER TABLE record_has_field_email_value DROP CONSTRAINT FK_7D1A9952A66B6A08');
        $this->addSql('ALTER TABLE record_report DROP CONSTRAINT FK_A67331FAABB75189');
        $this->addSql('ALTER TABLE record_report DROP CONSTRAINT FK_A67331FA464B2689');
        $this->addSql('ALTER TABLE record_has_field_text_value DROP CONSTRAINT FK_9BA7173ABB75189');
        $this->addSql('ALTER TABLE record_has_field_text_value DROP CONSTRAINT FK_9BA7173EEDC4C91');
        $this->addSql('ALTER TABLE record_has_field_text_value DROP CONSTRAINT FK_9BA7173A66B6A08');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE record_note DROP CONSTRAINT FK_2A0A6BC0DE12AB56');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE7E3C61F9');
        $this->addSql('ALTER TABLE record_has_field_url_value DROP CONSTRAINT FK_13D8416A443707B0');
        $this->addSql('ALTER TABLE record_has_field_boolean_value DROP CONSTRAINT FK_A65F2C36443707B0');
        $this->addSql('ALTER TABLE record_has_field_lat_lng_value DROP CONSTRAINT FK_5B806808443707B0');
        $this->addSql('ALTER TABLE record_has_field_string_value DROP CONSTRAINT FK_105079F5443707B0');
        $this->addSql('ALTER TABLE record_has_field_email_value DROP CONSTRAINT FK_7D1A9952443707B0');
        $this->addSql('ALTER TABLE record_has_field_text_value DROP CONSTRAINT FK_9BA7173443707B0');
        $this->addSql('ALTER TABLE record DROP CONSTRAINT FK_9B349F912C94069F');
        $this->addSql('ALTER TABLE field DROP CONSTRAINT FK_5BF545582C94069F');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7E7A1254A');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7166D1F9C');
        $this->addSql('ALTER TABLE directory DROP CONSTRAINT FK_467844DA166D1F9C');
        $this->addSql('ALTER TABLE contact DROP CONSTRAINT FK_4C62E638166D1F9C');
        $this->addSql('DROP SEQUENCE record_has_field_url_value_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE record_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE event_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE field_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE directory_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE record_has_field_boolean_value_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE contact_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE record_has_field_lat_lng_value_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE record_has_state_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE record_has_field_string_value_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE record_has_field_email_value_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE record_report_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE record_note_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE project_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE record_has_field_text_value_id_seq CASCADE');
        $this->addSql('DROP TABLE record_has_field_url_value');
        $this->addSql('DROP TABLE record');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE user_account');
        $this->addSql('DROP TABLE field');
        $this->addSql('DROP TABLE directory');
        $this->addSql('DROP TABLE record_has_field_boolean_value');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE record_has_field_lat_lng_value');
        $this->addSql('DROP TABLE record_has_state');
        $this->addSql('DROP TABLE record_has_field_string_value');
        $this->addSql('DROP TABLE record_has_field_email_value');
        $this->addSql('DROP TABLE record_report');
        $this->addSql('DROP TABLE record_note');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE record_has_field_text_value');
    }
}
