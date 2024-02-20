<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221205032056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_contact_contact_group (contact_id INT NOT NULL, contact_group_id INT NOT NULL, INDEX IDX_7062F34CE7A1254A (contact_id), INDEX IDX_7062F34C647145D0 (contact_group_id), PRIMARY KEY(contact_id, contact_group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_contact_group (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, tpoint_id INT DEFAULT NULL, tpoint_checksum VARCHAR(128) DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_region_contact (region_id INT NOT NULL, contact_id INT NOT NULL, INDEX IDX_7D762CE498260155 (region_id), INDEX IDX_7D762CE4E7A1254A (contact_id), PRIMARY KEY(region_id, contact_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_contact_contact_group ADD CONSTRAINT FK_7062F34CE7A1254A FOREIGN KEY (contact_id) REFERENCES pv_contact (id)');
        $this->addSql('ALTER TABLE pv_contact_contact_group ADD CONSTRAINT FK_7062F34C647145D0 FOREIGN KEY (contact_group_id) REFERENCES pv_contact_group (id)');
        $this->addSql('ALTER TABLE pv_region_contact ADD CONSTRAINT FK_7D762CE498260155 FOREIGN KEY (region_id) REFERENCES pv_region (id)');
        $this->addSql('ALTER TABLE pv_region_contact ADD CONSTRAINT FK_7D762CE4E7A1254A FOREIGN KEY (contact_id) REFERENCES pv_contact (id)');
        $this->addSql('ALTER TABLE pv_region ADD color VARCHAR(16) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact_contact_group DROP FOREIGN KEY FK_7062F34CE7A1254A');
        $this->addSql('ALTER TABLE pv_contact_contact_group DROP FOREIGN KEY FK_7062F34C647145D0');
        $this->addSql('ALTER TABLE pv_region_contact DROP FOREIGN KEY FK_7D762CE498260155');
        $this->addSql('ALTER TABLE pv_region_contact DROP FOREIGN KEY FK_7D762CE4E7A1254A');
        $this->addSql('DROP TABLE pv_contact_contact_group');
        $this->addSql('DROP TABLE pv_contact_group');
        $this->addSql('DROP TABLE pv_region_contact');
        $this->addSql('ALTER TABLE pv_region DROP color, DROP description');
    }
}
