<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170812075340 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');


        $this->addSql('ALTER TABLE user_account DROP locked');
        $this->addSql('ALTER TABLE user_account DROP expired');
        $this->addSql('ALTER TABLE user_account DROP expires_at');
        $this->addSql('ALTER TABLE user_account DROP credentials_expired');
        $this->addSql('ALTER TABLE user_account DROP credentials_expire_at');
        $this->addSql('ALTER TABLE user_account ALTER username TYPE VARCHAR(180)');
        $this->addSql('ALTER TABLE user_account ALTER username_canonical TYPE VARCHAR(180)');
        $this->addSql('ALTER TABLE user_account ALTER email TYPE VARCHAR(180)');
        $this->addSql('ALTER TABLE user_account ALTER email_canonical TYPE VARCHAR(180)');
        $this->addSql('ALTER TABLE user_account ALTER salt DROP NOT NULL');
        $this->addSql('ALTER TABLE user_account ALTER confirmation_token TYPE VARCHAR(180)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_253B48AEC05FB297 ON user_account (confirmation_token)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');

        $this->addSql('DROP INDEX UNIQ_253B48AEC05FB297');
        $this->addSql('ALTER TABLE user_account ADD locked BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE user_account ADD expired BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE user_account ADD expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE user_account ADD credentials_expired BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE user_account ADD credentials_expire_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE user_account ALTER username TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE user_account ALTER username_canonical TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE user_account ALTER email TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE user_account ALTER email_canonical TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE user_account ALTER salt SET NOT NULL');
        $this->addSql('ALTER TABLE user_account ALTER confirmation_token TYPE VARCHAR(255)');
    }
}
