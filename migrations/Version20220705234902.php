<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220705234902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $inbox = $this->connection->fetchAllAssociative('SELECT id, normalized_data FROM pv_inbox WHERE normalized_data LIKE "%+0000%"');

        foreach($inbox as $inboxItem) {

            $normalizedData = json_decode($inboxItem['normalized_data'], true);

            $normalizedData['startDate'] = (new \DateTime($normalizedData['startDate']))->format(\DateTimeInterface::ATOM);
            $normalizedData['endDate'] = (new \DateTime($normalizedData['endDate']))->format(\DateTimeInterface::ATOM);

            $this->connection->update('pv_inbox', [
                'normalized_data' => json_encode($normalizedData),
            ], [
                'id' => $inboxItem['id'],
            ]);

            $this->write(sprintf('Migrated dates for inbox item "%s".', $inboxItem['id']));

        }

    }

    public function down(Schema $schema): void
    {

    }
}
