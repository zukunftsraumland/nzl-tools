<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240909073437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_project_local_workgroups (project_id INT NOT NULL, local_workgroup_id INT NOT NULL, INDEX IDX_AB6F7B50166D1F9C (project_id), INDEX IDX_AB6F7B506ED20785 (local_workgroup_id), PRIMARY KEY(project_id, local_workgroup_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_project_local_workgroups ADD CONSTRAINT FK_AB6F7B50166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pv_project_local_workgroups ADD CONSTRAINT FK_AB6F7B506ED20785 FOREIGN KEY (local_workgroup_id) REFERENCES pv_local_workgroup (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_local_workgroups DROP FOREIGN KEY FK_8D0837FA6ED20785');
        $this->addSql('ALTER TABLE project_local_workgroups DROP FOREIGN KEY FK_8D0837FA166D1F9C');
        $this->addSql('DROP TABLE project_local_workgroups');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_local_workgroups (project_id INT NOT NULL, local_workgroup_id INT NOT NULL, INDEX IDX_8D0837FA166D1F9C (project_id), INDEX IDX_8D0837FA6ED20785 (local_workgroup_id), PRIMARY KEY(project_id, local_workgroup_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE project_local_workgroups ADD CONSTRAINT FK_8D0837FA6ED20785 FOREIGN KEY (local_workgroup_id) REFERENCES pv_local_workgroup (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_local_workgroups ADD CONSTRAINT FK_8D0837FA166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pv_project_local_workgroups DROP FOREIGN KEY FK_AB6F7B50166D1F9C');
        $this->addSql('ALTER TABLE pv_project_local_workgroups DROP FOREIGN KEY FK_AB6F7B506ED20785');
        $this->addSql('DROP TABLE pv_project_local_workgroups');
    }
}
