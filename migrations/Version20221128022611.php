<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221128022611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_city (id INT AUTO_INCREMENT NOT NULL, state_id INT DEFAULT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, zip_code VARCHAR(16) DEFAULT NULL, name LONGTEXT DEFAULT NULL, municipal_number INT DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_1A2364BB5D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_contact (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, type VARCHAR(64) DEFAULT NULL, company_name VARCHAR(128) DEFAULT NULL, gender VARCHAR(32) DEFAULT NULL, academic_title VARCHAR(32) DEFAULT NULL, first_name VARCHAR(128) DEFAULT NULL, last_name VARCHAR(128) DEFAULT NULL, street VARCHAR(128) DEFAULT NULL, zip_code VARCHAR(16) DEFAULT NULL, city VARCHAR(128) DEFAULT NULL, email VARCHAR(128) DEFAULT NULL, phone VARCHAR(128) DEFAULT NULL, website VARCHAR(128) DEFAULT NULL, description LONGTEXT DEFAULT NULL, tpoint_id INT DEFAULT NULL, tpoint_checksum VARCHAR(128) DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX context_idx (context), INDEX type_idx (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_employment (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, employee_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, position INT DEFAULT NULL, role VARCHAR(128) DEFAULT NULL, INDEX IDX_C933C31E979B1AD6 (company_id), INDEX IDX_C933C31E8C03F15C (employee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_region (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, type VARCHAR(64) DEFAULT NULL, name LONGTEXT DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_region_city (region_id INT NOT NULL, city_id INT NOT NULL, INDEX IDX_F050084998260155 (region_id), INDEX IDX_F05008498BAC62AF (city_id), PRIMARY KEY(region_id, city_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_city ADD CONSTRAINT FK_1A2364BB5D83CC1 FOREIGN KEY (state_id) REFERENCES pv_state (id)');
        $this->addSql('ALTER TABLE pv_employment ADD CONSTRAINT FK_C933C31E979B1AD6 FOREIGN KEY (company_id) REFERENCES pv_contact (id)');
        $this->addSql('ALTER TABLE pv_employment ADD CONSTRAINT FK_C933C31E8C03F15C FOREIGN KEY (employee_id) REFERENCES pv_contact (id)');
        $this->addSql('ALTER TABLE pv_region_city ADD CONSTRAINT FK_F050084998260155 FOREIGN KEY (region_id) REFERENCES pv_region (id)');
        $this->addSql('ALTER TABLE pv_region_city ADD CONSTRAINT FK_F05008498BAC62AF FOREIGN KEY (city_id) REFERENCES pv_city (id)');
        $this->addSql('ALTER TABLE pv_state ADD code VARCHAR(64) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_city DROP FOREIGN KEY FK_1A2364BB5D83CC1');
        $this->addSql('ALTER TABLE pv_employment DROP FOREIGN KEY FK_C933C31E979B1AD6');
        $this->addSql('ALTER TABLE pv_employment DROP FOREIGN KEY FK_C933C31E8C03F15C');
        $this->addSql('ALTER TABLE pv_region_city DROP FOREIGN KEY FK_F050084998260155');
        $this->addSql('ALTER TABLE pv_region_city DROP FOREIGN KEY FK_F05008498BAC62AF');
        $this->addSql('DROP TABLE pv_city');
        $this->addSql('DROP TABLE pv_contact');
        $this->addSql('DROP TABLE pv_employment');
        $this->addSql('DROP TABLE pv_region');
        $this->addSql('DROP TABLE pv_region_city');
        $this->addSql('ALTER TABLE pv_state DROP code');
    }
}
