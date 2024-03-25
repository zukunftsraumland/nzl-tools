<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240117140930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pv_contact ADD CONSTRAINT FK_CB728F9D727ACA70 FOREIGN KEY (parent_id) REFERENCES pv_contact (id)');
        $this->addSql('CREATE INDEX IDX_CB728F9D727ACA70 ON pv_contact (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact DROP FOREIGN KEY FK_CB728F9D727ACA70');
        $this->addSql('DROP INDEX IDX_CB728F9D727ACA70 ON pv_contact');
        $this->addSql('ALTER TABLE pv_contact DROP parent_id');
    }
}
