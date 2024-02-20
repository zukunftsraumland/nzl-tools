<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220705005857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if(!count($this->connection->fetchAllAssociative('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME LIKE "modern_%"'))) {
            return;
        }

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE modern_project_country DROP FOREIGN KEY FK_A9910308F92F3E70');
        $this->addSql('ALTER TABLE modern_event_language DROP FOREIGN KEY FK_68647E3171F7E88B');
        $this->addSql('ALTER TABLE modern_event_location DROP FOREIGN KEY FK_E221864F71F7E88B');
        $this->addSql('ALTER TABLE modern_event_topic DROP FOREIGN KEY FK_5CF22CE571F7E88B');
        $this->addSql('ALTER TABLE modern_project_geographic_region DROP FOREIGN KEY FK_55CE759381314161');
        $this->addSql('ALTER TABLE modern_project_instrument DROP FOREIGN KEY FK_1E7339DCF11D9C');
        $this->addSql('ALTER TABLE modern_event_language DROP FOREIGN KEY FK_68647E3182F1BAF4');
        $this->addSql('ALTER TABLE modern_event_location DROP FOREIGN KEY FK_E221864F64D218E');
        $this->addSql('ALTER TABLE modern_project_program DROP FOREIGN KEY FK_680FBDEA3EB8070A');
        $this->addSql('ALTER TABLE modern_project_country DROP FOREIGN KEY FK_A9910308166D1F9C');
        $this->addSql('ALTER TABLE modern_project_geographic_region DROP FOREIGN KEY FK_55CE7593166D1F9C');
        $this->addSql('ALTER TABLE modern_project_instrument DROP FOREIGN KEY FK_1E7339D166D1F9C');
        $this->addSql('ALTER TABLE modern_project_program DROP FOREIGN KEY FK_680FBDEA166D1F9C');
        $this->addSql('ALTER TABLE modern_project_state DROP FOREIGN KEY FK_EC1DEC7B166D1F9C');
        $this->addSql('ALTER TABLE modern_project_topic DROP FOREIGN KEY FK_D2CEE09B166D1F9C');
        $this->addSql('ALTER TABLE modern_project_state DROP FOREIGN KEY FK_EC1DEC7B5D83CC1');
        $this->addSql('ALTER TABLE modern_event_topic DROP FOREIGN KEY FK_5CF22CE51F55203D');
        $this->addSql('ALTER TABLE modern_project_topic DROP FOREIGN KEY FK_D2CEE09B1F55203D');
        $this->addSql('DROP TABLE modern_country');
        $this->addSql('DROP TABLE modern_event');
        $this->addSql('DROP TABLE modern_event_collection');
        $this->addSql('DROP TABLE modern_event_language');
        $this->addSql('DROP TABLE modern_event_location');
        $this->addSql('DROP TABLE modern_event_topic');
        $this->addSql('DROP TABLE modern_file');
        $this->addSql('DROP TABLE modern_geographic_region');
        $this->addSql('DROP TABLE modern_inbox');
        $this->addSql('DROP TABLE modern_instrument');
        $this->addSql('DROP TABLE modern_interactive_graphic');
        $this->addSql('DROP TABLE modern_language');
        $this->addSql('DROP TABLE modern_location');
        $this->addSql('DROP TABLE modern_program');
        $this->addSql('DROP TABLE modern_project');
        $this->addSql('DROP TABLE modern_project_collection');
        $this->addSql('DROP TABLE modern_project_country');
        $this->addSql('DROP TABLE modern_project_geographic_region');
        $this->addSql('DROP TABLE modern_project_instrument');
        $this->addSql('DROP TABLE modern_project_program');
        $this->addSql('DROP TABLE modern_project_state');
        $this->addSql('DROP TABLE modern_project_topic');
        $this->addSql('DROP TABLE modern_state');
        $this->addSql('DROP TABLE modern_topic');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE modern_country (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, synonyms LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', translations LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', isPublic TINYINT(1) NOT NULL, position INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_event (id INT AUTO_INCREMENT NOT NULL, isPublic TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, title LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, color VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, location VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, organizer VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, contact LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, registration LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, startDate DATETIME DEFAULT NULL, endDate DATETIME DEFAULT NULL, programs LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', links LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', images LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', files LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', translations LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', isPromotedDE TINYINT(1) NOT NULL, isPromotedFR TINYINT(1) NOT NULL, isPromotedIT TINYINT(1) NOT NULL, videos LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_event_collection (id INT AUTO_INCREMENT NOT NULL, isDynamic TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, title LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, selection LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', filters LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_event_language (event_id INT NOT NULL, language_id INT NOT NULL, INDEX IDX_68647E3171F7E88B (event_id), INDEX IDX_68647E3182F1BAF4 (language_id), PRIMARY KEY(event_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_event_location (event_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_E221864F71F7E88B (event_id), INDEX IDX_E221864F64D218E (location_id), PRIMARY KEY(event_id, location_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_event_topic (event_id INT NOT NULL, topic_id INT NOT NULL, INDEX IDX_5CF22CE571F7E88B (event_id), INDEX IDX_5CF22CE51F55203D (topic_id), PRIMARY KEY(event_id, topic_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_file (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, extension LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, mimeType LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, data LONGBLOB DEFAULT NULL, hash VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_geographic_region (id INT AUTO_INCREMENT NOT NULL, isPublic TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, synonyms LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', translations LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', position INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_inbox (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, source VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, foreignId VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, internalId VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, title LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, isMerged TINYINT(1) NOT NULL, mergedAt DATETIME DEFAULT NULL, data LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', normalizedData LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_instrument (id INT AUTO_INCREMENT NOT NULL, isPublic TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, synonyms LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', translations LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', position INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_interactive_graphic (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, title LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, svg LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, selector LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, config LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', start LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, copyright LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_language (id INT AUTO_INCREMENT NOT NULL, isPublic TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, context VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, synonyms LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', translations LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', position INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_location (id INT AUTO_INCREMENT NOT NULL, isPublic TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, context VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, synonyms LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', translations LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', position INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_program (id INT AUTO_INCREMENT NOT NULL, isPublic TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, synonyms LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', translations LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', position INT DEFAULT NULL, longName LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, url LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_project (id INT AUTO_INCREMENT NOT NULL, isPublic TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, projectCode VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, source VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, foreignId VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, title LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, keywords LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, projectCosts VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, financing LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', startDate DATETIME DEFAULT NULL, endDate DATETIME DEFAULT NULL, dates LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', contacts LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', links LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', videos LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', images LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', files LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', translations LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', random INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_project_collection (id INT AUTO_INCREMENT NOT NULL, isDynamic TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, title LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, selection LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', filters LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_project_country (project_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_A9910308166D1F9C (project_id), INDEX IDX_A9910308F92F3E70 (country_id), PRIMARY KEY(project_id, country_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_project_geographic_region (project_id INT NOT NULL, geographic_region_id INT NOT NULL, INDEX IDX_55CE7593166D1F9C (project_id), INDEX IDX_55CE759381314161 (geographic_region_id), PRIMARY KEY(project_id, geographic_region_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_project_instrument (project_id INT NOT NULL, instrument_id INT NOT NULL, INDEX IDX_1E7339D166D1F9C (project_id), INDEX IDX_1E7339DCF11D9C (instrument_id), PRIMARY KEY(project_id, instrument_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_project_program (project_id INT NOT NULL, program_id INT NOT NULL, INDEX IDX_680FBDEA166D1F9C (project_id), INDEX IDX_680FBDEA3EB8070A (program_id), PRIMARY KEY(project_id, program_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_project_state (project_id INT NOT NULL, state_id INT NOT NULL, INDEX IDX_EC1DEC7B166D1F9C (project_id), INDEX IDX_EC1DEC7B5D83CC1 (state_id), PRIMARY KEY(project_id, state_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_project_topic (project_id INT NOT NULL, topic_id INT NOT NULL, INDEX IDX_D2CEE09B166D1F9C (project_id), INDEX IDX_D2CEE09B1F55203D (topic_id), PRIMARY KEY(project_id, topic_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_state (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, synonyms LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', translations LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', isPublic TINYINT(1) NOT NULL, position INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE modern_topic (id INT AUTO_INCREMENT NOT NULL, isPublic TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, synonyms LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', translations LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', context VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, position INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE modern_event_language ADD CONSTRAINT FK_68647E3182F1BAF4 FOREIGN KEY (language_id) REFERENCES modern_language (id)');
        $this->addSql('ALTER TABLE modern_event_language ADD CONSTRAINT FK_68647E3171F7E88B FOREIGN KEY (event_id) REFERENCES modern_event (id)');
        $this->addSql('ALTER TABLE modern_event_location ADD CONSTRAINT FK_E221864F71F7E88B FOREIGN KEY (event_id) REFERENCES modern_event (id)');
        $this->addSql('ALTER TABLE modern_event_location ADD CONSTRAINT FK_E221864F64D218E FOREIGN KEY (location_id) REFERENCES modern_location (id)');
        $this->addSql('ALTER TABLE modern_event_topic ADD CONSTRAINT FK_5CF22CE571F7E88B FOREIGN KEY (event_id) REFERENCES modern_event (id)');
        $this->addSql('ALTER TABLE modern_event_topic ADD CONSTRAINT FK_5CF22CE51F55203D FOREIGN KEY (topic_id) REFERENCES modern_topic (id)');
        $this->addSql('ALTER TABLE modern_project_country ADD CONSTRAINT FK_A9910308F92F3E70 FOREIGN KEY (country_id) REFERENCES modern_country (id)');
        $this->addSql('ALTER TABLE modern_project_country ADD CONSTRAINT FK_A9910308166D1F9C FOREIGN KEY (project_id) REFERENCES modern_project (id)');
        $this->addSql('ALTER TABLE modern_project_geographic_region ADD CONSTRAINT FK_55CE759381314161 FOREIGN KEY (geographic_region_id) REFERENCES modern_geographic_region (id)');
        $this->addSql('ALTER TABLE modern_project_geographic_region ADD CONSTRAINT FK_55CE7593166D1F9C FOREIGN KEY (project_id) REFERENCES modern_project (id)');
        $this->addSql('ALTER TABLE modern_project_instrument ADD CONSTRAINT FK_1E7339DCF11D9C FOREIGN KEY (instrument_id) REFERENCES modern_instrument (id)');
        $this->addSql('ALTER TABLE modern_project_instrument ADD CONSTRAINT FK_1E7339D166D1F9C FOREIGN KEY (project_id) REFERENCES modern_project (id)');
        $this->addSql('ALTER TABLE modern_project_program ADD CONSTRAINT FK_680FBDEA3EB8070A FOREIGN KEY (program_id) REFERENCES modern_program (id)');
        $this->addSql('ALTER TABLE modern_project_program ADD CONSTRAINT FK_680FBDEA166D1F9C FOREIGN KEY (project_id) REFERENCES modern_project (id)');
        $this->addSql('ALTER TABLE modern_project_state ADD CONSTRAINT FK_EC1DEC7B5D83CC1 FOREIGN KEY (state_id) REFERENCES modern_state (id)');
        $this->addSql('ALTER TABLE modern_project_state ADD CONSTRAINT FK_EC1DEC7B166D1F9C FOREIGN KEY (project_id) REFERENCES modern_project (id)');
        $this->addSql('ALTER TABLE modern_project_topic ADD CONSTRAINT FK_D2CEE09B1F55203D FOREIGN KEY (topic_id) REFERENCES modern_topic (id)');
        $this->addSql('ALTER TABLE modern_project_topic ADD CONSTRAINT FK_D2CEE09B166D1F9C FOREIGN KEY (project_id) REFERENCES modern_project (id)');
    }
}
