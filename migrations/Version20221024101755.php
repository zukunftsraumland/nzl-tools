<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221024101755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_job (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, position INT NOT NULL, name LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, employer VARCHAR(255) DEFAULT NULL, contact LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, application_deadline DATETIME DEFAULT NULL, links LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', files LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_job_location (job_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_AFD42923BE04EA9 (job_id), INDEX IDX_AFD4292364D218E (location_id), PRIMARY KEY(job_id, location_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_job_location ADD CONSTRAINT FK_AFD42923BE04EA9 FOREIGN KEY (job_id) REFERENCES pv_job (id)');
        $this->addSql('ALTER TABLE pv_job_location ADD CONSTRAINT FK_AFD4292364D218E FOREIGN KEY (location_id) REFERENCES pv_location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_job_location DROP FOREIGN KEY FK_AFD42923BE04EA9');
        $this->addSql('ALTER TABLE pv_job_location DROP FOREIGN KEY FK_AFD4292364D218E');
        $this->addSql('DROP TABLE pv_job');
        $this->addSql('DROP TABLE pv_job_location');
    }
}
