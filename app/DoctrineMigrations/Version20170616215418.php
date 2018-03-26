<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170616215418 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directoki_record_has_field_email_value DROP locale');
        $this->addSql('ALTER TABLE directoki_record_has_field_string_value DROP locale');
        $this->addSql('ALTER TABLE directoki_record_has_field_text_value DROP locale');
        $this->addSql('ALTER TABLE directoki_record_has_field_url_value DROP locale');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        throw new \Exception('Can not go back on this one');
        // I'd have to deal with the not null thing and no-one will run this so there is no point.


    }
}
