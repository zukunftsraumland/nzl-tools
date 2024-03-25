<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240116124338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact ADD country_id INT DEFAULT NULL, ADD state_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pv_contact ADD CONSTRAINT FK_CB728F9DF92F3E70 FOREIGN KEY (country_id) REFERENCES pv_country (id)');
        $this->addSql('ALTER TABLE pv_contact ADD CONSTRAINT FK_CB728F9D5D83CC1 FOREIGN KEY (state_id) REFERENCES pv_state (id)');
        $this->addSql('CREATE INDEX IDX_CB728F9DF92F3E70 ON pv_contact (country_id)');
        $this->addSql('CREATE INDEX IDX_CB728F9D5D83CC1 ON pv_contact (state_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact DROP FOREIGN KEY FK_CB728F9DF92F3E70');
        $this->addSql('ALTER TABLE pv_contact DROP FOREIGN KEY FK_CB728F9D5D83CC1');
        $this->addSql('DROP INDEX IDX_CB728F9DF92F3E70 ON pv_contact');
        $this->addSql('DROP INDEX IDX_CB728F9D5D83CC1 ON pv_contact');
        $this->addSql('ALTER TABLE pv_contact DROP country_id, DROP state_id');
    }
}
