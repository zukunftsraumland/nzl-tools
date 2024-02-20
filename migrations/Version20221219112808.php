<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221219112808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_job_stint (job_id INT NOT NULL, stint_id INT NOT NULL, INDEX IDX_915FF25ABE04EA9 (job_id), INDEX IDX_915FF25A2B7E0175 (stint_id), PRIMARY KEY(job_id, stint_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_stint (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_job_stint ADD CONSTRAINT FK_915FF25ABE04EA9 FOREIGN KEY (job_id) REFERENCES pv_job (id)');
        $this->addSql('ALTER TABLE pv_job_stint ADD CONSTRAINT FK_915FF25A2B7E0175 FOREIGN KEY (stint_id) REFERENCES pv_stint (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_job_stint DROP FOREIGN KEY FK_915FF25ABE04EA9');
        $this->addSql('ALTER TABLE pv_job_stint DROP FOREIGN KEY FK_915FF25A2B7E0175');
        $this->addSql('DROP TABLE pv_job_stint');
        $this->addSql('DROP TABLE pv_stint');
    }
}
