<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230327024637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_post (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, title LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, text LONGTEXT DEFAULT NULL, date DATETIME DEFAULT NULL, links LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', videos LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', images LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', files LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_post_topic (post_id INT NOT NULL, topic_id INT NOT NULL, INDEX IDX_7C600D724B89032C (post_id), INDEX IDX_7C600D721F55203D (topic_id), PRIMARY KEY(post_id, topic_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_post_topic ADD CONSTRAINT FK_7C600D724B89032C FOREIGN KEY (post_id) REFERENCES pv_post (id)');
        $this->addSql('ALTER TABLE pv_post_topic ADD CONSTRAINT FK_7C600D721F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_post_topic DROP FOREIGN KEY FK_7C600D724B89032C');
        $this->addSql('ALTER TABLE pv_post_topic DROP FOREIGN KEY FK_7C600D721F55203D');
        $this->addSql('DROP TABLE pv_post');
        $this->addSql('DROP TABLE pv_post_topic');
    }
}
