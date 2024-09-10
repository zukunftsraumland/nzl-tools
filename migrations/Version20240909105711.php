<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240909105711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_project_synergy_fund_tags (project_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_6595E72F166D1F9C (project_id), INDEX IDX_6595E72FBAD26311 (tag_id), PRIMARY KEY(project_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_synergy_goal_tags (project_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_858E2C0B166D1F9C (project_id), INDEX IDX_858E2C0BBAD26311 (tag_id), PRIMARY KEY(project_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_project_synergy_fund_tags ADD CONSTRAINT FK_6595E72F166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_synergy_fund_tags ADD CONSTRAINT FK_6595E72FBAD26311 FOREIGN KEY (tag_id) REFERENCES pv_tag (id)');
        $this->addSql('ALTER TABLE pv_project_synergy_goal_tags ADD CONSTRAINT FK_858E2C0B166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_synergy_goal_tags ADD CONSTRAINT FK_858E2C0BBAD26311 FOREIGN KEY (tag_id) REFERENCES pv_tag (id)');
        $this->addSql('ALTER TABLE pv_project ADD synergy TINYINT(1) DEFAULT NULL, ADD synergyGoal TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project_synergy_fund_tags DROP FOREIGN KEY FK_6595E72F166D1F9C');
        $this->addSql('ALTER TABLE pv_project_synergy_fund_tags DROP FOREIGN KEY FK_6595E72FBAD26311');
        $this->addSql('ALTER TABLE pv_project_synergy_goal_tags DROP FOREIGN KEY FK_858E2C0B166D1F9C');
        $this->addSql('ALTER TABLE pv_project_synergy_goal_tags DROP FOREIGN KEY FK_858E2C0BBAD26311');
        $this->addSql('DROP TABLE pv_project_synergy_fund_tags');
        $this->addSql('DROP TABLE pv_project_synergy_goal_tags');
        $this->addSql('ALTER TABLE pv_project DROP synergy, DROP synergyGoal');
    }
}
