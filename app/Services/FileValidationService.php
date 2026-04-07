<?php

namespace App\Services;

class FileValidationService
{
    /**
     * Allowed MIME types mapped to their magic bytes
     */
    protected static array $magicBytes = [
        'application/pdf' => ['%PDF'],
        'application/msword' => ["\xD0\xCF\x11\xE0"],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ["PK\x03\x04"],
        'text/plain' => [],
        'text/rtf' => ['{\\rtf'],
        'image/jpeg' => ["\xFF\xD8\xFF"],
        'image/png' => ["\x89PNG"],
    ];

    /**
     * Validate file content matches declared MIME type
     */
    public static function validateFileContent(string $filePath, string $declaredMimeType): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        // Text files don't have specific magic bytes
        if (str_starts_with($declaredMimeType, 'text/')) {
            return true;
        }

        $signatures = self::$magicBytes[$declaredMimeType] ?? null;

        if ($signatures === null) {
            return false; // Unknown MIME type not allowed
        }

        if (empty($signatures)) {
            return true;
        }

        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 16);
        fclose($handle);

        if ($header === false) {
            return false;
        }

        foreach ($signatures as $signature) {
            if (str_starts_with($header, $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize filename to prevent path traversal
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove directory separators
        $filename = str_replace(['/', '\\', "\0"], '', $filename);

        // Remove leading dots (hidden files)
        $filename = ltrim($filename, '.');

        // Replace special characters
        $filename = preg_replace('/[^\p{L}\p{N}\s\-_.()]/u', '_', $filename);

        // Collapse multiple underscores/spaces
        $filename = preg_replace('/[_\s]+/', '_', $filename);

        // Ensure non-empty
        if (empty(trim($filename, '._'))) {
            $filename = 'document_' . time();
        }

        return $filename;
    }
}
