<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221021013410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_log (id BIGINT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, context VARCHAR(64) NOT NULL, category VARCHAR(64) DEFAULT NULL, action VARCHAR(64) DEFAULT NULL, value LONGTEXT DEFAULT NULL, referer VARCHAR(768) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, fingerprint VARCHAR(32) DEFAULT NULL, INDEX context_idx (context), INDEX category_idx (category), INDEX action_idx (action), INDEX referer_idx (referer), INDEX username_idx (username), INDEX fingerprint_idx (fingerprint), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE pv_log');
    }
}
