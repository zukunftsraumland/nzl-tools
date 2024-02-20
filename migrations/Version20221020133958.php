<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221020133958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_education (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, name LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, organizer VARCHAR(255) DEFAULT NULL, contact LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, text LONGTEXT DEFAULT NULL, links LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', videos LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', images LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', files LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_education_education_type (education_id INT NOT NULL, education_type_id INT NOT NULL, INDEX IDX_F20377C2CA1BD71 (education_id), INDEX IDX_F20377CD968E34B (education_type_id), PRIMARY KEY(education_id, education_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_education_language (education_id INT NOT NULL, language_id INT NOT NULL, INDEX IDX_429F83E2CA1BD71 (education_id), INDEX IDX_429F83E82F1BAF4 (language_id), PRIMARY KEY(education_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_education_location (education_id INT NOT NULL, location_id INT NOT NULL, INDEX IDX_8E6C00402CA1BD71 (education_id), INDEX IDX_8E6C004064D218E (location_id), PRIMARY KEY(education_id, location_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_education_education_type ADD CONSTRAINT FK_F20377C2CA1BD71 FOREIGN KEY (education_id) REFERENCES pv_education (id)');
        $this->addSql('ALTER TABLE pv_education_education_type ADD CONSTRAINT FK_F20377CD968E34B FOREIGN KEY (education_type_id) REFERENCES pv_education_type (id)');
        $this->addSql('ALTER TABLE pv_education_language ADD CONSTRAINT FK_429F83E2CA1BD71 FOREIGN KEY (education_id) REFERENCES pv_education (id)');
        $this->addSql('ALTER TABLE pv_education_language ADD CONSTRAINT FK_429F83E82F1BAF4 FOREIGN KEY (language_id) REFERENCES pv_language (id)');
        $this->addSql('ALTER TABLE pv_education_location ADD CONSTRAINT FK_8E6C00402CA1BD71 FOREIGN KEY (education_id) REFERENCES pv_education (id)');
        $this->addSql('ALTER TABLE pv_education_location ADD CONSTRAINT FK_8E6C004064D218E FOREIGN KEY (location_id) REFERENCES pv_location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_education_education_type DROP FOREIGN KEY FK_F20377C2CA1BD71');
        $this->addSql('ALTER TABLE pv_education_education_type DROP FOREIGN KEY FK_F20377CD968E34B');
        $this->addSql('ALTER TABLE pv_education_language DROP FOREIGN KEY FK_429F83E2CA1BD71');
        $this->addSql('ALTER TABLE pv_education_language DROP FOREIGN KEY FK_429F83E82F1BAF4');
        $this->addSql('ALTER TABLE pv_education_location DROP FOREIGN KEY FK_8E6C00402CA1BD71');
        $this->addSql('ALTER TABLE pv_education_location DROP FOREIGN KEY FK_8E6C004064D218E');
        $this->addSql('DROP TABLE pv_education');
        $this->addSql('DROP TABLE pv_education_education_type');
        $this->addSql('DROP TABLE pv_education_language');
        $this->addSql('DROP TABLE pv_education_location');
    }
}
