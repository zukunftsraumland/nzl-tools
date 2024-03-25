<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240131184544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_contact_group_employment (contact_group_id INT NOT NULL, employment_id INT NOT NULL, INDEX IDX_8F58BB14647145D0 (contact_group_id), INDEX IDX_8F58BB14466E61E3 (employment_id), PRIMARY KEY(contact_group_id, employment_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_contact_group_employment ADD CONSTRAINT FK_8F58BB14647145D0 FOREIGN KEY (contact_group_id) REFERENCES pv_contact_group (id)');
        $this->addSql('ALTER TABLE pv_contact_group_employment ADD CONSTRAINT FK_8F58BB14466E61E3 FOREIGN KEY (employment_id) REFERENCES pv_employment (id)');
        $this->addSql('ALTER TABLE pv_contact ADD official_employment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pv_contact ADD CONSTRAINT FK_CB728F9DAE77198 FOREIGN KEY (official_employment_id) REFERENCES pv_employment (id)');
        $this->addSql('CREATE INDEX IDX_CB728F9DAE77198 ON pv_contact (official_employment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact_group_employment DROP FOREIGN KEY FK_8F58BB14647145D0');
        $this->addSql('ALTER TABLE pv_contact_group_employment DROP FOREIGN KEY FK_8F58BB14466E61E3');
        $this->addSql('DROP TABLE pv_contact_group_employment');
        $this->addSql('ALTER TABLE pv_contact DROP FOREIGN KEY FK_CB728F9DAE77198');
        $this->addSql('DROP INDEX IDX_CB728F9DAE77198 ON pv_contact');
        $this->addSql('ALTER TABLE pv_contact DROP official_employment_id');
    }
}
