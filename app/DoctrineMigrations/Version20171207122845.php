<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171207122845 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $result = $this->connection->fetchAssoc('SELECT MAX(id) AS id FROM directoki_select_value');

        $this->addSql('CREATE SEQUENCE directoki_select_value_has_title_id_seq INCREMENT BY 1 MINVALUE 1 START '. ($result['id']+1));
        $this->addSql('CREATE TABLE directoki_select_value_has_title (id INT NOT NULL, select_value_id INT NOT NULL, locale_id INT NOT NULL, creation_event_id INT NOT NULL, title TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ED30A613EB49157C ON directoki_select_value_has_title (select_value_id)');
        $this->addSql('CREATE INDEX IDX_ED30A613E559DFD1 ON directoki_select_value_has_title (locale_id)');
        $this->addSql('CREATE INDEX IDX_ED30A613ABB75189 ON directoki_select_value_has_title (creation_event_id)');
        $this->addSql('ALTER TABLE directoki_select_value_has_title ADD CONSTRAINT FK_ED30A613EB49157C FOREIGN KEY (select_value_id) REFERENCES directoki_select_value (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directoki_select_value_has_title ADD CONSTRAINT FK_ED30A613E559DFD1 FOREIGN KEY (locale_id) REFERENCES directoki_locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directoki_select_value_has_title ADD CONSTRAINT FK_ED30A613ABB75189 FOREIGN KEY (creation_event_id) REFERENCES directoki_event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql(
            'INSERT INTO directoki_select_value_has_title (id, select_value_id, locale_id, creation_event_id, title, created_at) '.
            ' SELECT sv.id, sv.id, l.id, sv.creation_event_id, sv.title, sv.created_at FROM directoki_select_value AS sv '.
            ' JOIN directoki_field AS f on sv.field_id = f.id '.
            ' JOIN directoki_directory AS d ON f.directory_id = d.id '.
            ' JOIN directoki_project AS p on d.project_id = p.id '.
            ' JOIN (SELECT project_id, MIN(id) AS id FROM directoki_locale GROUP BY project_id) AS l ON l.project_id = p.id '
        );

        $this->addSql('ALTER TABLE directoki_select_value ADD cached_titles JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE directoki_select_value DROP title');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE directoki_select_value_has_title_id_seq CASCADE');
        $this->addSql('DROP TABLE directoki_select_value_has_title');

        $this->addSql('ALTER TABLE directoki_select_value ADD title VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE directoki_select_value DROP cached_titles');

    }
}
