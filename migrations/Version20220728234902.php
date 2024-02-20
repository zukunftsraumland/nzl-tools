<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220728234902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $inbox = $this->connection->fetchAllAssociative('SELECT id, normalized_data FROM pv_inbox WHERE type = "project" AND normalized_data NOT LIKE "%businessSectors%"');

        foreach($inbox as $inboxItem) {

            $normalizedData = json_decode($inboxItem['normalized_data'], true);

            $normalizedData['businessSectors'] = [];

            $this->connection->update('pv_inbox', [
                'normalized_data' => json_encode($normalizedData),
            ], [
                'id' => $inboxItem['id'],
            ]);

            $this->write(sprintf('Added businessSectors to inbox item "%s".', $inboxItem['id']));

        }

    }

    public function down(Schema $schema): void
    {

    }
}
