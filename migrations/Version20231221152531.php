<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231221152531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact_group ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pv_contact_group ADD CONSTRAINT FK_297A9B31727ACA70 FOREIGN KEY (parent_id) REFERENCES pv_contact_group (id)');
        $this->addSql('CREATE INDEX IDX_297A9B31727ACA70 ON pv_contact_group (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact_group DROP FOREIGN KEY FK_297A9B31727ACA70');
        $this->addSql('DROP INDEX IDX_297A9B31727ACA70 ON pv_contact_group');
        $this->addSql('ALTER TABLE pv_contact_group DROP parent_id');
    }
}
