<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220812152341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE transport_start_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE transport_start (id INT NOT NULL, transport_id INT NOT NULL, times JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_276F2779909C13F ON transport_start (transport_id)');
        $this->addSql('ALTER TABLE transport_start ADD CONSTRAINT FK_276F2779909C13F FOREIGN KEY (transport_id) REFERENCES transport (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX uniq_66ab212e96901f54');
        $this->addSql('ALTER TABLE transport_run ALTER arrival_time TYPE INT USING cast(to_char((arrival_time)::TIMESTAMP,\'yyyymmddhhmiss\') as BigInt) ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE transport_start_id_seq CASCADE');
        $this->addSql('ALTER TABLE transport_start DROP CONSTRAINT FK_276F2779909C13F');
        $this->addSql('DROP TABLE transport_start');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
        $this->addSql('DROP INDEX UNIQ_8D93D6497BA2F5EB');
        $this->addSql('DROP INDEX "primary"');
        $this->addSql('ALTER TABLE "user" ALTER api_token DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_66ab212e96901f54 ON transport (number, type_id)');
        $this->addSql('ALTER TABLE transport_run ALTER arrival_time TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
    }
}
