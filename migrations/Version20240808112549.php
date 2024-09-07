<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240808112549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project DROP FOREIGN KEY FK_A8A3B94BAB93A16F');
        $this->addSql('DROP INDEX IDX_A8A3B94BAB93A16F ON pv_project');
        $this->addSql('ALTER TABLE pv_project DROP cooperation_project_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project ADD cooperation_project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pv_project ADD CONSTRAINT FK_A8A3B94BAB93A16F FOREIGN KEY (cooperation_project_id) REFERENCES pv_cooperation_project (id)');
        $this->addSql('CREATE INDEX IDX_A8A3B94BAB93A16F ON pv_project (cooperation_project_id)');
    }
}
