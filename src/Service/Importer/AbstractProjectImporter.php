<?php

namespace App\Service\Importer;

use App\Entity\Project;
use App\Entity\ProjectImport;
use App\Entity\User;
use App\Entity\LEPeriod;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Abstract base class for project importers
 * 
 * This class defines the common interface and functionality for all project importers.
 * Specific importers should extend this class and implement the abstract methods.
 */
abstract class AbstractProjectImporter
{
    protected EntityManagerInterface $em;
    protected SluggerInterface $slugger;
    protected string $uploadDir;

    public function __construct(
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        string $uploadDir
    ) {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->uploadDir = $uploadDir;
    }

    /**
     * Get the name of the importer
     * 
     * @return string The name of the importer
     */
    abstract public function getName(): string;

    /**
     * Get the description of the importer
     * 
     * @return string The description of the importer
     */
    abstract public function getDescription(): string;

    /**
     * Get the type of the importer
     * 
     * @return string The type of the importer (used for identification)
     */
    abstract public function getType(): string;

    /**
     * Process a single import item
     * 
     * @param ProjectImport $import The import record
     * @param int $rowIndex The row index to process
     * @return array The result of processing the row
     */
    abstract public function processImportItem(ProjectImport $import, int $rowIndex): array;

    /**
     * Prepare the project payload from the import data
     * 
     * @param array $data The data from the Excel row
     * @return array The prepared project payload
     */
    abstract protected function prepareProjectPayload(array $data): array;

    /**
     * Count the number of rows in an Excel file (excluding the header row)
     * 
     * @param string $filePath The path to the Excel file
     * @return int The number of rows in the file
     */
    public function countRows(string $filePath): int
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get the highest row number and subtract header rows
            // The number of header rows may vary by importer type
            return $worksheet->getHighestRow() - $this->getHeaderRowCount();
        } catch (\Exception $e) {
            
            throw new \Exception('Failed to count rows: ' . $e->getMessage());
        }
    }

    /**
     * Get the number of header rows in the Excel file
     * 
     * @return int The number of header rows
     */
    abstract protected function getHeaderRowCount(): int;

    /**
     * Generate a preview of the import data
     * 
     * @param ProjectImport $import The import record
     * @return array The preview data
     */
    abstract public function generatePreview(ProjectImport $import): array;

    /**
     * Import projects from an Excel file
     * 
     * @param ProjectImport $import The import record
     * @param User $user The user who initiated the import
     * @param LEPeriod|null $lePeriod Optional LE Period to assign to all imported projects
     * @param array|null $selectedRows Optional array of row numbers to import (if null, all rows will be imported)
     * @return bool True if the import was successful, false otherwise
     */
    abstract public function importProjects(ProjectImport $import, User $user, ?LEPeriod $lePeriod = null, ?array $selectedRows = null): bool;

    /**
     * Find a project by title
     * 
     * @param string $title The project title
     * @return Project|null The project, or null if not found
     */
    protected function findProjectByTitle(string $title): ?Project
    {
        return $this->em->getRepository(Project::class)->findOneBy(['title' => $title]);
    }

    /**
     * Check if a string looks like a URL
     * 
     * @param string $string The string to check
     * @return bool True if the string looks like a URL, false otherwise
     */
    protected function looksLikeUrl($string): bool
    {
        // Check if the string is a URL
        // This checks for common URL patterns with or without protocol
        return (
            // Has protocol
            preg_match('~^(?:f|ht)tps?://~i', $string) > 0 ||
            // Starts with www.
            preg_match('~^www\.~i', $string) > 0 ||
            // Contains a domain extension
            preg_match('~\.(com|org|net|gov|edu|info|biz|at|de|ch|eu)(/|\b)~i', $string) > 0
        );
    }

    /**
     * Determines if a file is an image based on its extension
     * 
     * @param string $filename The filename to check
     * @return bool True if the file is an image, false otherwise
     */
    protected function isImageFile(string $filename): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'tiff', 'tif'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Handle case where extension might have uppercase letters
        if (empty($extension) && strpos($filename, '.') !== false) {
            $parts = explode('.', $filename);
            $extension = strtolower(end($parts));
        }
        
        return in_array($extension, $imageExtensions);
    }

    /**
     * Gets the mime type based on file extension
     * 
     * @param string $filename The filename to check
     * @return string The mime type
     */
    protected function getMimeTypeFromFilename(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Handle case where extension might have uppercase letters
        if (empty($extension) && strpos($filename, '.') !== false) {
            $parts = explode('.', $filename);
            $extension = strtolower(end($parts));
        }
        
        $mimeTypes = [
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            
            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
} 