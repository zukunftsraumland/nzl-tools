<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912070803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project ADD funding_method LONGTEXT DEFAULT NULL, ADD funding_method_stakeholders LONGTEXT DEFAULT NULL, ADD results_quantity LONGTEXT DEFAULT NULL, ADD result_quality LONGTEXT DEFAULT NULL, DROP synergy, DROP synergyGoal');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project ADD synergy TINYINT(1) DEFAULT NULL, ADD synergyGoal TINYINT(1) DEFAULT NULL, DROP funding_method, DROP funding_method_stakeholders, DROP results_quantity, DROP result_quality');
    }
}
