<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220824133149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorite_transport (user_id INT NOT NULL, transport_id INT NOT NULL, PRIMARY KEY(user_id, transport_id))');
        $this->addSql('CREATE INDEX IDX_ACBBB4BCA76ED395 ON favorite_transport (user_id)');
        $this->addSql('CREATE INDEX IDX_ACBBB4BC9909C13F ON favorite_transport (transport_id)');
        $this->addSql('ALTER TABLE favorite_transport ADD CONSTRAINT FK_ACBBB4BCA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorite_transport ADD CONSTRAINT FK_ACBBB4BC9909C13F FOREIGN KEY (transport_id) REFERENCES transport (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE favorite_transport DROP CONSTRAINT FK_ACBBB4BCA76ED395');
        $this->addSql('ALTER TABLE favorite_transport DROP CONSTRAINT FK_ACBBB4BC9909C13F');
        $this->addSql('DROP TABLE favorite_transport');
    }
}
