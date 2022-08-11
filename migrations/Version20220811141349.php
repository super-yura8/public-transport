<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220811141349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE transport_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE transport_run_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE transport_stop_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE transport_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE transport (id INT NOT NULL, type_id INT NOT NULL, number INT NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_66AB212E96901F54 ON transport (number, type_id)');
        $this->addSql('CREATE INDEX IDX_66AB212EC54C8C93 ON transport (type_id)');
        $this->addSql('CREATE TABLE transport_run (id INT NOT NULL, transport_stop_id INT NOT NULL, transport_id INT NOT NULL, arrival_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D5DE8AFADD5C6CC ON transport_run (transport_stop_id)');
        $this->addSql('CREATE INDEX IDX_8D5DE8AF9909C13F ON transport_run (transport_id)');
        $this->addSql('CREATE TABLE transport_stop (id INT NOT NULL, address VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64864133D4E6F81 ON transport_stop (address)');
        $this->addSql('CREATE TABLE transport_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_510E00AC5E237E06 ON transport_type (name)');
        $this->addSql('ALTER TABLE transport ADD CONSTRAINT FK_66AB212EC54C8C93 FOREIGN KEY (type_id) REFERENCES transport_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transport_run ADD CONSTRAINT FK_8D5DE8AFADD5C6CC FOREIGN KEY (transport_stop_id) REFERENCES transport_stop (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transport_run ADD CONSTRAINT FK_8D5DE8AF9909C13F FOREIGN KEY (transport_id) REFERENCES transport (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE transport_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE transport_run_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE transport_stop_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE transport_type_id_seq CASCADE');
        $this->addSql('ALTER TABLE transport DROP CONSTRAINT FK_66AB212EC54C8C93');
        $this->addSql('ALTER TABLE transport_run DROP CONSTRAINT FK_8D5DE8AFADD5C6CC');
        $this->addSql('ALTER TABLE transport_run DROP CONSTRAINT FK_8D5DE8AF9909C13F');
        $this->addSql('DROP TABLE transport');
        $this->addSql('DROP TABLE transport_run');
        $this->addSql('DROP TABLE transport_stop');
        $this->addSql('DROP TABLE transport_type');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
        $this->addSql('DROP INDEX UNIQ_8D93D6497BA2F5EB');
        $this->addSql('DROP INDEX "primary"');
        $this->addSql('ALTER TABLE "user" ALTER api_token DROP NOT NULL');
    }
}
