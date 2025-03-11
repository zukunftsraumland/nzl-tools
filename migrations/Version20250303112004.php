<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303112004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_project_import (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, total_rows INT DEFAULT NULL, processed_rows INT DEFAULT NULL, successful_rows INT DEFAULT NULL, error_rows INT DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, INDEX IDX_A0EC9E0CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_import_item (id INT AUTO_INCREMENT NOT NULL, import_id INT NOT NULL, project_id INT DEFAULT NULL, row_number INT NOT NULL, status VARCHAR(20) NOT NULL, raw_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', processed_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', error_message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_D4AEBBBEB6A263D9 (import_id), INDEX IDX_D4AEBBBE166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_project_import ADD CONSTRAINT FK_A0EC9E0CA76ED395 FOREIGN KEY (user_id) REFERENCES pv_user (id)');
        $this->addSql('ALTER TABLE pv_project_import_item ADD CONSTRAINT FK_D4AEBBBEB6A263D9 FOREIGN KEY (import_id) REFERENCES pv_project_import (id)');
        $this->addSql('ALTER TABLE pv_project_import_item ADD CONSTRAINT FK_D4AEBBBE166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project_import DROP FOREIGN KEY FK_A0EC9E0CA76ED395');
        $this->addSql('ALTER TABLE pv_project_import_item DROP FOREIGN KEY FK_D4AEBBBEB6A263D9');
        $this->addSql('ALTER TABLE pv_project_import_item DROP FOREIGN KEY FK_D4AEBBBE166D1F9C');
        $this->addSql('DROP TABLE pv_project_import');
        $this->addSql('DROP TABLE pv_project_import_item');
    }
}
