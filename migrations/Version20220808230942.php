<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220808230942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_authority (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, is_state_supported TINYINT(1) NOT NULL, context VARCHAR(255) DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_beneficiary (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_financial_support (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, is_public TINYINT(1) NOT NULL, position INT NOT NULL, name LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, policies VARCHAR(255) DEFAULT NULL, additional_information LONGTEXT DEFAULT NULL, inclusion_criteria LONGTEXT DEFAULT NULL, exclusion_criteria LONGTEXT DEFAULT NULL, application LONGTEXT DEFAULT NULL, financing_ratio LONGTEXT DEFAULT NULL, res LONGTEXT DEFAULT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, logo LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', links LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', contacts LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_financial_support_authority (financial_support_id INT NOT NULL, authority_id INT NOT NULL, INDEX IDX_D11CDA5A8677442C (financial_support_id), INDEX IDX_D11CDA5A81EC865B (authority_id), PRIMARY KEY(financial_support_id, authority_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_financial_support_beneficiary (financial_support_id INT NOT NULL, beneficiary_id INT NOT NULL, INDEX IDX_4983776C8677442C (financial_support_id), INDEX IDX_4983776CECCAAFA0 (beneficiary_id), PRIMARY KEY(financial_support_id, beneficiary_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_financial_support_topic (financial_support_id INT NOT NULL, topic_id INT NOT NULL, INDEX IDX_511A52D08677442C (financial_support_id), INDEX IDX_511A52D01F55203D (topic_id), PRIMARY KEY(financial_support_id, topic_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_financial_support_project_type (financial_support_id INT NOT NULL, project_type_id INT NOT NULL, INDEX IDX_5C1F06378677442C (financial_support_id), INDEX IDX_5C1F0637535280F6 (project_type_id), PRIMARY KEY(financial_support_id, project_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_financial_support_instrument (financial_support_id INT NOT NULL, instrument_id INT NOT NULL, INDEX IDX_3918AB08677442C (financial_support_id), INDEX IDX_3918AB0CF11D9C (instrument_id), PRIMARY KEY(financial_support_id, instrument_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_financial_support_geographic_region (financial_support_id INT NOT NULL, geographic_region_id INT NOT NULL, INDEX IDX_1AFAB2968677442C (financial_support_id), INDEX IDX_1AFAB29681314161 (geographic_region_id), PRIMARY KEY(financial_support_id, geographic_region_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_financial_support_state (financial_support_id INT NOT NULL, state_id INT NOT NULL, INDEX IDX_6FC95E308677442C (financial_support_id), INDEX IDX_6FC95E305D83CC1 (state_id), PRIMARY KEY(financial_support_id, state_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_type (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_financial_support_authority ADD CONSTRAINT FK_D11CDA5A8677442C FOREIGN KEY (financial_support_id) REFERENCES pv_financial_support (id)');
        $this->addSql('ALTER TABLE pv_financial_support_authority ADD CONSTRAINT FK_D11CDA5A81EC865B FOREIGN KEY (authority_id) REFERENCES pv_authority (id)');
        $this->addSql('ALTER TABLE pv_financial_support_beneficiary ADD CONSTRAINT FK_4983776C8677442C FOREIGN KEY (financial_support_id) REFERENCES pv_financial_support (id)');
        $this->addSql('ALTER TABLE pv_financial_support_beneficiary ADD CONSTRAINT FK_4983776CECCAAFA0 FOREIGN KEY (beneficiary_id) REFERENCES pv_beneficiary (id)');
        $this->addSql('ALTER TABLE pv_financial_support_topic ADD CONSTRAINT FK_511A52D08677442C FOREIGN KEY (financial_support_id) REFERENCES pv_financial_support (id)');
        $this->addSql('ALTER TABLE pv_financial_support_topic ADD CONSTRAINT FK_511A52D01F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id)');
        $this->addSql('ALTER TABLE pv_financial_support_project_type ADD CONSTRAINT FK_5C1F06378677442C FOREIGN KEY (financial_support_id) REFERENCES pv_financial_support (id)');
        $this->addSql('ALTER TABLE pv_financial_support_project_type ADD CONSTRAINT FK_5C1F0637535280F6 FOREIGN KEY (project_type_id) REFERENCES pv_project_type (id)');
        $this->addSql('ALTER TABLE pv_financial_support_instrument ADD CONSTRAINT FK_3918AB08677442C FOREIGN KEY (financial_support_id) REFERENCES pv_financial_support (id)');
        $this->addSql('ALTER TABLE pv_financial_support_instrument ADD CONSTRAINT FK_3918AB0CF11D9C FOREIGN KEY (instrument_id) REFERENCES pv_instrument (id)');
        $this->addSql('ALTER TABLE pv_financial_support_geographic_region ADD CONSTRAINT FK_1AFAB2968677442C FOREIGN KEY (financial_support_id) REFERENCES pv_financial_support (id)');
        $this->addSql('ALTER TABLE pv_financial_support_geographic_region ADD CONSTRAINT FK_1AFAB29681314161 FOREIGN KEY (geographic_region_id) REFERENCES pv_geographic_region (id)');
        $this->addSql('ALTER TABLE pv_financial_support_state ADD CONSTRAINT FK_6FC95E308677442C FOREIGN KEY (financial_support_id) REFERENCES pv_financial_support (id)');
        $this->addSql('ALTER TABLE pv_financial_support_state ADD CONSTRAINT FK_6FC95E305D83CC1 FOREIGN KEY (state_id) REFERENCES pv_state (id)');
        $this->addSql('ALTER TABLE pv_topic ADD icon LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pv_geographic_region ADD context VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pv_instrument ADD context VARCHAR(255) DEFAULT NULL');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->update('pv_geographic_region', [
            'context' => 'project',
        ], [
            'context' => null,
        ]);
        $this->connection->update('pv_instrument', [
            'context' => 'project',
        ], [
            'context' => null,
        ]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_financial_support_authority DROP FOREIGN KEY FK_D11CDA5A81EC865B');
        $this->addSql('ALTER TABLE pv_financial_support_beneficiary DROP FOREIGN KEY FK_4983776CECCAAFA0');
        $this->addSql('ALTER TABLE pv_financial_support_authority DROP FOREIGN KEY FK_D11CDA5A8677442C');
        $this->addSql('ALTER TABLE pv_financial_support_beneficiary DROP FOREIGN KEY FK_4983776C8677442C');
        $this->addSql('ALTER TABLE pv_financial_support_topic DROP FOREIGN KEY FK_511A52D08677442C');
        $this->addSql('ALTER TABLE pv_financial_support_project_type DROP FOREIGN KEY FK_5C1F06378677442C');
        $this->addSql('ALTER TABLE pv_financial_support_instrument DROP FOREIGN KEY FK_3918AB08677442C');
        $this->addSql('ALTER TABLE pv_financial_support_geographic_region DROP FOREIGN KEY FK_1AFAB2968677442C');
        $this->addSql('ALTER TABLE pv_financial_support_state DROP FOREIGN KEY FK_6FC95E308677442C');
        $this->addSql('ALTER TABLE pv_financial_support_project_type DROP FOREIGN KEY FK_5C1F0637535280F6');
        $this->addSql('DROP TABLE pv_authority');
        $this->addSql('DROP TABLE pv_beneficiary');
        $this->addSql('DROP TABLE pv_financial_support');
        $this->addSql('DROP TABLE pv_financial_support_authority');
        $this->addSql('DROP TABLE pv_financial_support_beneficiary');
        $this->addSql('DROP TABLE pv_financial_support_topic');
        $this->addSql('DROP TABLE pv_financial_support_project_type');
        $this->addSql('DROP TABLE pv_financial_support_instrument');
        $this->addSql('DROP TABLE pv_financial_support_geographic_region');
        $this->addSql('DROP TABLE pv_financial_support_state');
        $this->addSql('DROP TABLE pv_project_type');
        $this->addSql('ALTER TABLE pv_topic DROP icon');
        $this->addSql('ALTER TABLE pv_geographic_region DROP context');
        $this->addSql('ALTER TABLE pv_instrument DROP context');
    }
}
