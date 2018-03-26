<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170213194754 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE record_has_field_multi_select_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE select_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE record_has_field_multi_select_value (id INT NOT NULL, select_value_id INT NOT NULL, field_id INT NOT NULL, record_id INT NOT NULL, addition_creation_event_id INT NOT NULL, addition_approval_event_id INT DEFAULT NULL, addition_refusal_event_id INT DEFAULT NULL, removal_creation_event_id INT DEFAULT NULL, removal_approval_event_id INT DEFAULT NULL, removal_refusal_event_id INT DEFAULT NULL, addition_created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, addition_approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, addition_refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, removal_created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, removal_approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, removal_refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FA080C6EB49157C ON record_has_field_multi_select_value (select_value_id)');
        $this->addSql('CREATE INDEX IDX_FA080C6443707B0 ON record_has_field_multi_select_value (field_id)');
        $this->addSql('CREATE INDEX IDX_FA080C64DFD750C ON record_has_field_multi_select_value (record_id)');
        $this->addSql('CREATE INDEX IDX_FA080C61C4CD815 ON record_has_field_multi_select_value (addition_creation_event_id)');
        $this->addSql('CREATE INDEX IDX_FA080C65927C50D ON record_has_field_multi_select_value (addition_approval_event_id)');
        $this->addSql('CREATE INDEX IDX_FA080C6E0BECD76 ON record_has_field_multi_select_value (addition_refusal_event_id)');
        $this->addSql('CREATE INDEX IDX_FA080C67B98784 ON record_has_field_multi_select_value (removal_creation_event_id)');
        $this->addSql('CREATE INDEX IDX_FA080C642D29A9C ON record_has_field_multi_select_value (removal_approval_event_id)');
        $this->addSql('CREATE INDEX IDX_FA080C614442723 ON record_has_field_multi_select_value (removal_refusal_event_id)');
        $this->addSql('CREATE TABLE select_value (id INT NOT NULL, field_id INT NOT NULL, creation_event_id INT NOT NULL, public_id VARCHAR(250) NOT NULL, title VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B463C96F443707B0 ON select_value (field_id)');
        $this->addSql('CREATE INDEX IDX_B463C96FABB75189 ON select_value (creation_event_id)');
        $this->addSql('CREATE UNIQUE INDEX select_value_public_id ON select_value (field_id, public_id)');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value ADD CONSTRAINT FK_FA080C6EB49157C FOREIGN KEY (select_value_id) REFERENCES select_value (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value ADD CONSTRAINT FK_FA080C6443707B0 FOREIGN KEY (field_id) REFERENCES field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value ADD CONSTRAINT FK_FA080C64DFD750C FOREIGN KEY (record_id) REFERENCES record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value ADD CONSTRAINT FK_FA080C61C4CD815 FOREIGN KEY (addition_creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value ADD CONSTRAINT FK_FA080C65927C50D FOREIGN KEY (addition_approval_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value ADD CONSTRAINT FK_FA080C6E0BECD76 FOREIGN KEY (addition_refusal_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value ADD CONSTRAINT FK_FA080C67B98784 FOREIGN KEY (removal_creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value ADD CONSTRAINT FK_FA080C642D29A9C FOREIGN KEY (removal_approval_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value ADD CONSTRAINT FK_FA080C614442723 FOREIGN KEY (removal_refusal_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE select_value ADD CONSTRAINT FK_B463C96F443707B0 FOREIGN KEY (field_id) REFERENCES field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE select_value ADD CONSTRAINT FK_B463C96FABB75189 FOREIGN KEY (creation_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE record_has_field_multi_select_value DROP CONSTRAINT FK_FA080C6EB49157C');
        $this->addSql('DROP SEQUENCE record_has_field_multi_select_value_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE select_value_id_seq CASCADE');
        $this->addSql('DROP TABLE record_has_field_multi_select_value');
        $this->addSql('DROP TABLE select_value');
    }
}
