<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251031142545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment (id SERIAL NOT NULL, client_id INT NOT NULL, technician_id INT NOT NULL, start_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FE38F84419EB6921 ON appointment (client_id)');
        $this->addSql('CREATE INDEX IDX_FE38F844E6C5D496 ON appointment (technician_id)');
        $this->addSql('COMMENT ON COLUMN appointment.start_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN appointment.end_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE availability_slot (id SERIAL NOT NULL, technician_id INT NOT NULL, start_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_booked BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1C11DC9EE6C5D496 ON availability_slot (technician_id)');
        $this->addSql('COMMENT ON COLUMN availability_slot.start_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN availability_slot.end_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F84419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844E6C5D496 FOREIGN KEY (technician_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE availability_slot ADD CONSTRAINT FK_1C11DC9EE6C5D496 FOREIGN KEY (technician_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE appointment DROP CONSTRAINT FK_FE38F84419EB6921');
        $this->addSql('ALTER TABLE appointment DROP CONSTRAINT FK_FE38F844E6C5D496');
        $this->addSql('ALTER TABLE availability_slot DROP CONSTRAINT FK_1C11DC9EE6C5D496');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE availability_slot');
    }
}
