<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220728233837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_business_sector (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_business_sector (project_id INT NOT NULL, business_sector_id INT NOT NULL, INDEX IDX_FC369572166D1F9C (project_id), INDEX IDX_FC369572C7F1CE18 (business_sector_id), PRIMARY KEY(project_id, business_sector_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_project_business_sector ADD CONSTRAINT FK_FC369572166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_business_sector ADD CONSTRAINT FK_FC369572C7F1CE18 FOREIGN KEY (business_sector_id) REFERENCES pv_business_sector (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project_business_sector DROP FOREIGN KEY FK_FC369572C7F1CE18');
        $this->addSql('DROP TABLE pv_business_sector');
        $this->addSql('DROP TABLE pv_project_business_sector');
    }
}
