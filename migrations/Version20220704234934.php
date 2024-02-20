<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220704234934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_country (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_event (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, is_promoted_de TINYINT(1) NOT NULL, is_promoted_fr TINYINT(1) NOT NULL, is_promoted_it TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, title LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, organizer VARCHAR(255) DEFAULT NULL, contact LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, text LONGTEXT DEFAULT NULL, registration LONGTEXT DEFAULT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, programs LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', links LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', videos LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', images LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', files LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_event_topic (event_id INT NOT NULL, topic_id INT NOT NULL, INDEX IDX_595861CB71F7E88B (event_id), INDEX IDX_595861CB1F55203D (topic_id), PRIMARY KEY(event_id, topic_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_event_language (event_id INT NOT NULL, language_id INT NOT NULL, INDEX IDX_29636E6071F7E88B (event_id), INDEX IDX_29636E6082F1BAF4 (language_id), PRIMARY KEY(event_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_event_location (event_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_A326961E71F7E88B (event_id), INDEX IDX_A326961E64D218E (location_id), PRIMARY KEY(event_id, location_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_event_collection (id INT AUTO_INCREMENT NOT NULL, is_dynamic TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, title LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, selection LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', filters LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_file (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, extension LONGTEXT DEFAULT NULL, mime_type LONGTEXT DEFAULT NULL, data LONGBLOB DEFAULT NULL, hash VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_geographic_region (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_inbox (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, source VARCHAR(255) NOT NULL, foreign_id VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, internal_id VARCHAR(255) DEFAULT NULL, title LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, is_merged TINYINT(1) NOT NULL, merged_at DATETIME DEFAULT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', normalized_data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_instrument (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_interactive_graphic (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, title LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, svg LONGTEXT DEFAULT NULL, selector LONGTEXT DEFAULT NULL, start LONGTEXT DEFAULT NULL, copyright LONGTEXT DEFAULT NULL, config LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_language (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_location (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_program (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, long_name LONGTEXT DEFAULT NULL, url LONGTEXT DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, project_code VARCHAR(255) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, foreign_id VARCHAR(255) DEFAULT NULL, random INT DEFAULT NULL, title LONGTEXT DEFAULT NULL, keywords LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, project_costs VARCHAR(255) DEFAULT NULL, financing LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, dates LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', contacts LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', links LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', videos LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', images LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', files LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_topic (project_id INT NOT NULL, topic_id INT NOT NULL, INDEX IDX_D1A4D130166D1F9C (project_id), INDEX IDX_D1A4D1301F55203D (topic_id), PRIMARY KEY(project_id, topic_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_geographic_region (project_id INT NOT NULL, geographic_region_id INT NOT NULL, INDEX IDX_4A065925166D1F9C (project_id), INDEX IDX_4A06592581314161 (geographic_region_id), PRIMARY KEY(project_id, geographic_region_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_country (project_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_B5BC657A166D1F9C (project_id), INDEX IDX_B5BC657AF92F3E70 (country_id), PRIMARY KEY(project_id, country_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_state (project_id INT NOT NULL, state_id INT NOT NULL, INDEX IDX_EF77DDD0166D1F9C (project_id), INDEX IDX_EF77DDD05D83CC1 (state_id), PRIMARY KEY(project_id, state_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_program (project_id INT NOT NULL, program_id INT NOT NULL, INDEX IDX_7422DB98166D1F9C (project_id), INDEX IDX_7422DB983EB8070A (program_id), PRIMARY KEY(project_id, program_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_instrument (project_id INT NOT NULL, instrument_id INT NOT NULL, INDEX IDX_20EA1FEB166D1F9C (project_id), INDEX IDX_20EA1FEBCF11D9C (instrument_id), PRIMARY KEY(project_id, instrument_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_collection (id INT AUTO_INCREMENT NOT NULL, is_dynamic TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, title LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, selection LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', filters LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_state (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_topic (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_event_topic ADD CONSTRAINT FK_595861CB71F7E88B FOREIGN KEY (event_id) REFERENCES pv_event (id)');
        $this->addSql('ALTER TABLE pv_event_topic ADD CONSTRAINT FK_595861CB1F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id)');
        $this->addSql('ALTER TABLE pv_event_language ADD CONSTRAINT FK_29636E6071F7E88B FOREIGN KEY (event_id) REFERENCES pv_event (id)');
        $this->addSql('ALTER TABLE pv_event_language ADD CONSTRAINT FK_29636E6082F1BAF4 FOREIGN KEY (language_id) REFERENCES pv_language (id)');
        $this->addSql('ALTER TABLE pv_event_location ADD CONSTRAINT FK_A326961E71F7E88B FOREIGN KEY (event_id) REFERENCES pv_event (id)');
        $this->addSql('ALTER TABLE pv_event_location ADD CONSTRAINT FK_A326961E64D218E FOREIGN KEY (location_id) REFERENCES pv_location (id)');
        $this->addSql('ALTER TABLE pv_project_topic ADD CONSTRAINT FK_D1A4D130166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_topic ADD CONSTRAINT FK_D1A4D1301F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id)');
        $this->addSql('ALTER TABLE pv_project_geographic_region ADD CONSTRAINT FK_4A065925166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_geographic_region ADD CONSTRAINT FK_4A06592581314161 FOREIGN KEY (geographic_region_id) REFERENCES pv_geographic_region (id)');
        $this->addSql('ALTER TABLE pv_project_country ADD CONSTRAINT FK_B5BC657A166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_country ADD CONSTRAINT FK_B5BC657AF92F3E70 FOREIGN KEY (country_id) REFERENCES pv_country (id)');
        $this->addSql('ALTER TABLE pv_project_state ADD CONSTRAINT FK_EF77DDD0166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_state ADD CONSTRAINT FK_EF77DDD05D83CC1 FOREIGN KEY (state_id) REFERENCES pv_state (id)');
        $this->addSql('ALTER TABLE pv_project_program ADD CONSTRAINT FK_7422DB98166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_program ADD CONSTRAINT FK_7422DB983EB8070A FOREIGN KEY (program_id) REFERENCES pv_program (id)');
        $this->addSql('ALTER TABLE pv_project_instrument ADD CONSTRAINT FK_20EA1FEB166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_instrument ADD CONSTRAINT FK_20EA1FEBCF11D9C FOREIGN KEY (instrument_id) REFERENCES pv_instrument (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project_country DROP FOREIGN KEY FK_B5BC657AF92F3E70');
        $this->addSql('ALTER TABLE pv_event_topic DROP FOREIGN KEY FK_595861CB71F7E88B');
        $this->addSql('ALTER TABLE pv_event_language DROP FOREIGN KEY FK_29636E6071F7E88B');
        $this->addSql('ALTER TABLE pv_event_location DROP FOREIGN KEY FK_A326961E71F7E88B');
        $this->addSql('ALTER TABLE pv_project_geographic_region DROP FOREIGN KEY FK_4A06592581314161');
        $this->addSql('ALTER TABLE pv_project_instrument DROP FOREIGN KEY FK_20EA1FEBCF11D9C');
        $this->addSql('ALTER TABLE pv_event_language DROP FOREIGN KEY FK_29636E6082F1BAF4');
        $this->addSql('ALTER TABLE pv_event_location DROP FOREIGN KEY FK_A326961E64D218E');
        $this->addSql('ALTER TABLE pv_project_program DROP FOREIGN KEY FK_7422DB983EB8070A');
        $this->addSql('ALTER TABLE pv_project_topic DROP FOREIGN KEY FK_D1A4D130166D1F9C');
        $this->addSql('ALTER TABLE pv_project_geographic_region DROP FOREIGN KEY FK_4A065925166D1F9C');
        $this->addSql('ALTER TABLE pv_project_country DROP FOREIGN KEY FK_B5BC657A166D1F9C');
        $this->addSql('ALTER TABLE pv_project_state DROP FOREIGN KEY FK_EF77DDD0166D1F9C');
        $this->addSql('ALTER TABLE pv_project_program DROP FOREIGN KEY FK_7422DB98166D1F9C');
        $this->addSql('ALTER TABLE pv_project_instrument DROP FOREIGN KEY FK_20EA1FEB166D1F9C');
        $this->addSql('ALTER TABLE pv_project_state DROP FOREIGN KEY FK_EF77DDD05D83CC1');
        $this->addSql('ALTER TABLE pv_event_topic DROP FOREIGN KEY FK_595861CB1F55203D');
        $this->addSql('ALTER TABLE pv_project_topic DROP FOREIGN KEY FK_D1A4D1301F55203D');
        $this->addSql('DROP TABLE pv_country');
        $this->addSql('DROP TABLE pv_event');
        $this->addSql('DROP TABLE pv_event_topic');
        $this->addSql('DROP TABLE pv_event_language');
        $this->addSql('DROP TABLE pv_event_location');
        $this->addSql('DROP TABLE pv_event_collection');
        $this->addSql('DROP TABLE pv_file');
        $this->addSql('DROP TABLE pv_geographic_region');
        $this->addSql('DROP TABLE pv_inbox');
        $this->addSql('DROP TABLE pv_instrument');
        $this->addSql('DROP TABLE pv_interactive_graphic');
        $this->addSql('DROP TABLE pv_language');
        $this->addSql('DROP TABLE pv_location');
        $this->addSql('DROP TABLE pv_program');
        $this->addSql('DROP TABLE pv_project');
        $this->addSql('DROP TABLE pv_project_topic');
        $this->addSql('DROP TABLE pv_project_geographic_region');
        $this->addSql('DROP TABLE pv_project_country');
        $this->addSql('DROP TABLE pv_project_state');
        $this->addSql('DROP TABLE pv_project_program');
        $this->addSql('DROP TABLE pv_project_instrument');
        $this->addSql('DROP TABLE pv_project_collection');
        $this->addSql('DROP TABLE pv_state');
        $this->addSql('DROP TABLE pv_topic');
    }
}
