<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240913092706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_le_funding_article (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_1B9B100812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_le_funding_category (id INT AUTO_INCREMENT NOT NULL, period_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_AC5FF480EC8B7ADE (period_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_le_funding_method (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_937125F67294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_le_period (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_local_workgroup (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_local_workgroups (project_id INT NOT NULL, local_workgroup_id INT NOT NULL, INDEX IDX_AB6F7B50166D1F9C (project_id), INDEX IDX_AB6F7B506ED20785 (local_workgroup_id), PRIMARY KEY(project_id, local_workgroup_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_synergy_fund_tags (project_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_6595E72F166D1F9C (project_id), INDEX IDX_6595E72FBAD26311 (tag_id), PRIMARY KEY(project_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_project_synergy_goal_tags (project_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_858E2C0B166D1F9C (project_id), INDEX IDX_858E2C0BBAD26311 (tag_id), PRIMARY KEY(project_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_le_funding_article ADD CONSTRAINT FK_1B9B100812469DE2 FOREIGN KEY (category_id) REFERENCES pv_le_funding_category (id)');
        $this->addSql('ALTER TABLE pv_le_funding_category ADD CONSTRAINT FK_AC5FF480EC8B7ADE FOREIGN KEY (period_id) REFERENCES pv_le_period (id)');
        $this->addSql('ALTER TABLE pv_le_funding_method ADD CONSTRAINT FK_937125F67294869C FOREIGN KEY (article_id) REFERENCES pv_le_funding_article (id)');
        $this->addSql('ALTER TABLE pv_project_local_workgroups ADD CONSTRAINT FK_AB6F7B50166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pv_project_local_workgroups ADD CONSTRAINT FK_AB6F7B506ED20785 FOREIGN KEY (local_workgroup_id) REFERENCES pv_local_workgroup (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pv_project_synergy_fund_tags ADD CONSTRAINT FK_6595E72F166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_synergy_fund_tags ADD CONSTRAINT FK_6595E72FBAD26311 FOREIGN KEY (tag_id) REFERENCES pv_tag (id)');
        $this->addSql('ALTER TABLE pv_project_synergy_goal_tags ADD CONSTRAINT FK_858E2C0B166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_synergy_goal_tags ADD CONSTRAINT FK_858E2C0BBAD26311 FOREIGN KEY (tag_id) REFERENCES pv_tag (id)');
        $this->addSql('ALTER TABLE pv_project ADD local_workgroup_id INT DEFAULT NULL, ADD le_period_id INT DEFAULT NULL, ADD le_funding_category_id INT DEFAULT NULL, ADD le_funding_article_id INT DEFAULT NULL, ADD le_funding_method_id INT DEFAULT NULL, ADD cooperation_project_at TINYINT(1) DEFAULT NULL, ADD cooperation_project_eu TINYINT(1) DEFAULT NULL, ADD case_study TINYINT(1) DEFAULT NULL, ADD exemplary LONGTEXT DEFAULT NULL, ADD initial_context LONGTEXT DEFAULT NULL, ADD initial_context_goals LONGTEXT DEFAULT NULL, ADD additional_value LONGTEXT DEFAULT NULL, ADD additional_value_result LONGTEXT DEFAULT NULL, ADD innovations LONGTEXT DEFAULT NULL, ADD integration_young_citizen LONGTEXT DEFAULT NULL, ADD integration_female_citizen LONGTEXT DEFAULT NULL, ADD integration_minorities LONGTEXT DEFAULT NULL, ADD learning_experience LONGTEXT DEFAULT NULL, ADD transferable LONGTEXT DEFAULT NULL, ADD transfer_details LONGTEXT DEFAULT NULL, ADD funding_method LONGTEXT DEFAULT NULL, ADD funding_method_stakeholders LONGTEXT DEFAULT NULL, ADD results_quantity LONGTEXT DEFAULT NULL, ADD results_quality LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pv_project ADD CONSTRAINT FK_A8A3B94B6ED20785 FOREIGN KEY (local_workgroup_id) REFERENCES pv_local_workgroup (id)');
        $this->addSql('ALTER TABLE pv_project ADD CONSTRAINT FK_A8A3B94B35936E29 FOREIGN KEY (le_period_id) REFERENCES pv_le_period (id)');
        $this->addSql('ALTER TABLE pv_project ADD CONSTRAINT FK_A8A3B94B7B6696B2 FOREIGN KEY (le_funding_category_id) REFERENCES pv_le_funding_category (id)');
        $this->addSql('ALTER TABLE pv_project ADD CONSTRAINT FK_A8A3B94B545D280F FOREIGN KEY (le_funding_article_id) REFERENCES pv_le_funding_article (id)');
        $this->addSql('ALTER TABLE pv_project ADD CONSTRAINT FK_A8A3B94B9160657 FOREIGN KEY (le_funding_method_id) REFERENCES pv_le_funding_method (id)');
        $this->addSql('CREATE INDEX IDX_A8A3B94B6ED20785 ON pv_project (local_workgroup_id)');
        $this->addSql('CREATE INDEX IDX_A8A3B94B35936E29 ON pv_project (le_period_id)');
        $this->addSql('CREATE INDEX IDX_A8A3B94B7B6696B2 ON pv_project (le_funding_category_id)');
        $this->addSql('CREATE INDEX IDX_A8A3B94B545D280F ON pv_project (le_funding_article_id)');
        $this->addSql('CREATE INDEX IDX_A8A3B94B9160657 ON pv_project (le_funding_method_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project DROP FOREIGN KEY FK_A8A3B94B545D280F');
        $this->addSql('ALTER TABLE pv_project DROP FOREIGN KEY FK_A8A3B94B7B6696B2');
        $this->addSql('ALTER TABLE pv_project DROP FOREIGN KEY FK_A8A3B94B9160657');
        $this->addSql('ALTER TABLE pv_project DROP FOREIGN KEY FK_A8A3B94B35936E29');
        $this->addSql('ALTER TABLE pv_project DROP FOREIGN KEY FK_A8A3B94B6ED20785');
        $this->addSql('ALTER TABLE pv_le_funding_article DROP FOREIGN KEY FK_1B9B100812469DE2');
        $this->addSql('ALTER TABLE pv_le_funding_category DROP FOREIGN KEY FK_AC5FF480EC8B7ADE');
        $this->addSql('ALTER TABLE pv_le_funding_method DROP FOREIGN KEY FK_937125F67294869C');
        $this->addSql('ALTER TABLE pv_project_local_workgroups DROP FOREIGN KEY FK_AB6F7B50166D1F9C');
        $this->addSql('ALTER TABLE pv_project_local_workgroups DROP FOREIGN KEY FK_AB6F7B506ED20785');
        $this->addSql('ALTER TABLE pv_project_synergy_fund_tags DROP FOREIGN KEY FK_6595E72F166D1F9C');
        $this->addSql('ALTER TABLE pv_project_synergy_fund_tags DROP FOREIGN KEY FK_6595E72FBAD26311');
        $this->addSql('ALTER TABLE pv_project_synergy_goal_tags DROP FOREIGN KEY FK_858E2C0B166D1F9C');
        $this->addSql('ALTER TABLE pv_project_synergy_goal_tags DROP FOREIGN KEY FK_858E2C0BBAD26311');
        $this->addSql('DROP TABLE pv_le_funding_article');
        $this->addSql('DROP TABLE pv_le_funding_category');
        $this->addSql('DROP TABLE pv_le_funding_method');
        $this->addSql('DROP TABLE pv_le_period');
        $this->addSql('DROP TABLE pv_local_workgroup');
        $this->addSql('DROP TABLE pv_project_local_workgroups');
        $this->addSql('DROP TABLE pv_project_synergy_fund_tags');
        $this->addSql('DROP TABLE pv_project_synergy_goal_tags');
        $this->addSql('DROP INDEX IDX_A8A3B94B6ED20785 ON pv_project');
        $this->addSql('DROP INDEX IDX_A8A3B94B35936E29 ON pv_project');
        $this->addSql('DROP INDEX IDX_A8A3B94B7B6696B2 ON pv_project');
        $this->addSql('DROP INDEX IDX_A8A3B94B545D280F ON pv_project');
        $this->addSql('DROP INDEX IDX_A8A3B94B9160657 ON pv_project');
        $this->addSql('ALTER TABLE pv_project DROP local_workgroup_id, DROP le_period_id, DROP le_funding_category_id, DROP le_funding_article_id, DROP le_funding_method_id, DROP cooperation_project_at, DROP cooperation_project_eu, DROP case_study, DROP exemplary, DROP initial_context, DROP initial_context_goals, DROP additional_value, DROP additional_value_result, DROP innovations, DROP integration_young_citizen, DROP integration_female_citizen, DROP integration_minorities, DROP learning_experience, DROP transferable, DROP transfer_details, DROP funding_method, DROP funding_method_stakeholders, DROP results_quantity, DROP results_quality');
    }
}
