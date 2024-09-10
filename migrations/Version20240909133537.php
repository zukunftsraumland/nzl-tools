<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240909133537 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project CHANGE le_period_id le_period_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pv_project ADD CONSTRAINT FK_A8A3B94B35936E29 FOREIGN KEY (le_period_id) REFERENCES leperiod (id)');
        $this->addSql('ALTER TABLE pv_project ADD CONSTRAINT FK_A8A3B94B7B6696B2 FOREIGN KEY (le_funding_category_id) REFERENCES lefunding_category (id)');
        $this->addSql('ALTER TABLE pv_project ADD CONSTRAINT FK_A8A3B94B545D280F FOREIGN KEY (le_funding_article_id) REFERENCES lefunding_article (id)');
        $this->addSql('ALTER TABLE pv_project ADD CONSTRAINT FK_A8A3B94B9160657 FOREIGN KEY (le_funding_method_id) REFERENCES lefunding_method (id)');
        $this->addSql('CREATE INDEX IDX_A8A3B94B35936E29 ON pv_project (le_period_id)');
        $this->addSql('CREATE INDEX IDX_A8A3B94B7B6696B2 ON pv_project (le_funding_category_id)');
        $this->addSql('CREATE INDEX IDX_A8A3B94B545D280F ON pv_project (le_funding_article_id)');
        $this->addSql('CREATE INDEX IDX_A8A3B94B9160657 ON pv_project (le_funding_method_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project DROP FOREIGN KEY FK_A8A3B94B35936E29');
        $this->addSql('ALTER TABLE pv_project DROP FOREIGN KEY FK_A8A3B94B7B6696B2');
        $this->addSql('ALTER TABLE pv_project DROP FOREIGN KEY FK_A8A3B94B545D280F');
        $this->addSql('ALTER TABLE pv_project DROP FOREIGN KEY FK_A8A3B94B9160657');
        $this->addSql('DROP INDEX IDX_A8A3B94B35936E29 ON pv_project');
        $this->addSql('DROP INDEX IDX_A8A3B94B7B6696B2 ON pv_project');
        $this->addSql('DROP INDEX IDX_A8A3B94B545D280F ON pv_project');
        $this->addSql('DROP INDEX IDX_A8A3B94B9160657 ON pv_project');
        $this->addSql('ALTER TABLE pv_project CHANGE le_period_id le_period_id INT NOT NULL');
    }
}
