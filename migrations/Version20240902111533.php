<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240902111533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project ADD exemplary LONGTEXT DEFAULT NULL, ADD initial_context LONGTEXT DEFAULT NULL, ADD initial_context_goals LONGTEXT DEFAULT NULL, ADD additional_value LONGTEXT DEFAULT NULL, ADD additional_value_result LONGTEXT DEFAULT NULL, ADD innovations LONGTEXT DEFAULT NULL, ADD integration_young_citizen LONGTEXT DEFAULT NULL, ADD integration_female_citizen LONGTEXT DEFAULT NULL, ADD integration_minorities LONGTEXT DEFAULT NULL, ADD learning_experience LONGTEXT DEFAULT NULL, ADD transferable LONGTEXT DEFAULT NULL, ADD transfer_details LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_project DROP exemplary, DROP initial_context, DROP initial_context_goals, DROP additional_value, DROP additional_value_result, DROP innovations, DROP integration_young_citizen, DROP integration_female_citizen, DROP integration_minorities, DROP learning_experience, DROP transferable, DROP transfer_details');
    }
}
