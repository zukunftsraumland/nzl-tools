<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240116132354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact ADD language_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pv_contact ADD CONSTRAINT FK_CB728F9D82F1BAF4 FOREIGN KEY (language_id) REFERENCES pv_language (id)');
        $this->addSql('CREATE INDEX IDX_CB728F9D82F1BAF4 ON pv_contact (language_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact DROP FOREIGN KEY FK_CB728F9D82F1BAF4');
        $this->addSql('DROP INDEX IDX_CB728F9D82F1BAF4 ON pv_contact');
        $this->addSql('ALTER TABLE pv_contact DROP language_id');
    }
}
