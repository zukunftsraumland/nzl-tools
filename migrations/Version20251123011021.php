<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123011021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX hash_idx ON pv_file');
        $this->addSql('ALTER TABLE pv_file DROP data, DROP hash');
        $this->addSql('CREATE INDEX file_hash_idx ON pv_file (file_hash)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX file_hash_idx ON pv_file');
        $this->addSql('ALTER TABLE pv_file ADD data LONGBLOB DEFAULT NULL, ADD hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX hash_idx ON pv_file (hash)');
    }
}
