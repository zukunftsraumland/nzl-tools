<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220704234935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    protected function migrateTableData($tableName)
    {
        $columnsTo = $this->connection->fetchAllAssociative('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "pv_'.$tableName.'"');
        $columnsFrom = $this->connection->fetchAllAssociative('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "modern_'.$tableName.'"');

        $columnsTo = array_map(function ($row) {
            return $row['COLUMN_NAME'];
        }, $columnsTo);
        sort($columnsTo);

        $columnsFrom = array_map(function ($row) {
            return $row['COLUMN_NAME'];
        }, $columnsFrom);
        sort($columnsFrom);

        $this->addSql('INSERT INTO pv_'.$tableName.' ('.implode(', ', $columnsTo).') SELECT '.implode(', ', $columnsFrom).' FROM modern_'.$tableName);
    }

    public function up(Schema $schema): void
    {
        if(!count($this->connection->fetchAllAssociative('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME LIKE "modern_%"'))) {
            return;
        }

        $this->migrateTableData('country');
        $this->migrateTableData('event');
        $this->migrateTableData('file');
        $this->migrateTableData('geographic_region');
        $this->migrateTableData('inbox');
        $this->migrateTableData('instrument');
        $this->migrateTableData('interactive_graphic');
        $this->migrateTableData('language');
        $this->migrateTableData('location');
        $this->migrateTableData('program');
        $this->migrateTableData('project');
        $this->migrateTableData('state');
        $this->migrateTableData('topic');
        $this->migrateTableData('project_collection');

        $this->migrateTableData('event_topic');
        $this->migrateTableData('event_language');
        $this->migrateTableData('event_location');
        $this->migrateTableData('event_collection');

        $this->migrateTableData('project_topic');
        $this->migrateTableData('project_geographic_region');
        $this->migrateTableData('project_country');
        $this->migrateTableData('project_state');
        $this->migrateTableData('project_program');
        $this->migrateTableData('project_instrument');
    }

    public function down(Schema $schema): void
    {

    }
}
