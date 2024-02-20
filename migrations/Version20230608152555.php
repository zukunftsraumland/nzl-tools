<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230608152555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_project_tag (project_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_955F702A166D1F9C (project_id), INDEX IDX_955F702ABAD26311 (tag_id), PRIMARY KEY(project_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_project_tag ADD CONSTRAINT FK_955F702A166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_tag ADD CONSTRAINT FK_955F702ABAD26311 FOREIGN KEY (tag_id) REFERENCES pv_tag (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project_tag DROP FOREIGN KEY FK_955F702A166D1F9C');
        $this->addSql('ALTER TABLE pv_project_tag DROP FOREIGN KEY FK_955F702ABAD26311');
        $this->addSql('DROP TABLE pv_project_tag');
    }
}
