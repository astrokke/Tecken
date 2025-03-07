<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708100953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity ADD end_date DATE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN activity.end_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE milestone ADD start_date DATE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN milestone.start_date IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE activity DROP end_date');
        $this->addSql('ALTER TABLE milestone DROP start_date');
    }
}
