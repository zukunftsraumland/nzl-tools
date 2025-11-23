<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123010248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
    }

    public function postUp(Schema $schema): void
    {
        $files = $this->connection->fetchAllAssociative('SELECT id, name, extension FROM pv_file WHERE file_path IS NULL ORDER BY id ASC');

        foreach($files as $file) {

            $logMessage = 'Applying data migration '.basename(__FILE__, '.php').' to file '.$file['name'].' (ID: '.$file['id'].')';

            $this->write($logMessage);

            $fileData = $this->connection->fetchOne('SELECT data FROM pv_file WHERE id = :id', [
                'id' => $file['id'],
            ]);
            $fileData = count(explode(';base64,', $fileData)) >= 2 ? explode(';base64,', $fileData, 2)[1] : $fileData;
            $fileData = base64_decode($fileData);
            $fileHash = md5($fileData);
            $fileDir = 'var/storage/files/'.substr($fileHash, 0, 2);
            $filePath = $fileDir.'/'.$fileHash.'.'.$file['extension'];

            if(!is_dir(__DIR__.'/../'.$fileDir)) {
                mkdir(__DIR__.'/../'.$fileDir, 0777, true);
            }

            file_put_contents(__DIR__.'/../'.$filePath, $fileData);

            unset($fileData);

            $updateData = [
                'file_path' => $filePath,
                'file_hash' => $fileHash,
            ];

            $this->connection->update('pv_file', $updateData, [
                'id' => $file['id'],
            ]);

        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
