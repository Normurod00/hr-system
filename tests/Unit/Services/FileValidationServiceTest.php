<?php

namespace Tests\Unit\Services;

use App\Services\FileValidationService;
use PHPUnit\Framework\TestCase;

class FileValidationServiceTest extends TestCase
{
    // ========== sanitizeFilename tests ==========

    public function test_sanitize_filename_removes_directory_separators(): void
    {
        $this->assertStringNotContainsString('/', FileValidationService::sanitizeFilename('path/to/file.pdf'));
        $this->assertStringNotContainsString('\\', FileValidationService::sanitizeFilename('path\\to\\file.pdf'));
    }

    public function test_sanitize_filename_removes_leading_dots(): void
    {
        $result = FileValidationService::sanitizeFilename('.htaccess');
        $this->assertDoesNotMatchRegularExpression('/^\./', $result);
    }

    public function test_sanitize_filename_handles_only_dots(): void
    {
        $result = FileValidationService::sanitizeFilename('...');
        $this->assertNotEmpty($result);
        $this->assertStringStartsWith('document_', $result);
    }

    public function test_sanitize_filename_preserves_valid_names(): void
    {
        $this->assertSame('resume.pdf', FileValidationService::sanitizeFilename('resume.pdf'));
    }

    public function test_sanitize_filename_replaces_spaces_with_underscore(): void
    {
        $result = FileValidationService::sanitizeFilename('my file.docx');
        $this->assertStringNotContainsString(' ', $result);
        $this->assertSame('my_file.docx', $result);
    }

    public function test_sanitize_filename_handles_unicode(): void
    {
        $result = FileValidationService::sanitizeFilename('Резюме_Иванов.pdf');
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('.pdf', $result);
    }

    public function test_sanitize_filename_removes_null_bytes(): void
    {
        $result = FileValidationService::sanitizeFilename("file\0name.pdf");
        $this->assertStringNotContainsString("\0", $result);
    }

    public function test_sanitize_filename_collapses_multiple_underscores(): void
    {
        $result = FileValidationService::sanitizeFilename('file___name.pdf');
        $this->assertStringNotContainsString('___', $result);
    }

    // ========== validateFileContent tests ==========

    public function test_validate_file_content_returns_false_for_nonexistent_file(): void
    {
        $this->assertFalse(FileValidationService::validateFileContent('/nonexistent/file', 'application/pdf'));
    }

    public function test_validate_file_content_allows_text_files(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, 'Hello World');

        $this->assertTrue(FileValidationService::validateFileContent($tmpFile, 'text/plain'));

        unlink($tmpFile);
    }

    public function test_validate_file_content_allows_text_rtf(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, 'some content');

        // text/* types bypass magic byte check
        $this->assertTrue(FileValidationService::validateFileContent($tmpFile, 'text/rtf'));

        unlink($tmpFile);
    }

    public function test_validate_file_content_rejects_unknown_mime(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, 'some content');

        $this->assertFalse(FileValidationService::validateFileContent($tmpFile, 'application/x-unknown'));

        unlink($tmpFile);
    }

    public function test_validate_file_content_validates_pdf(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, '%PDF-1.4 fake pdf content');

        $this->assertTrue(FileValidationService::validateFileContent($tmpFile, 'application/pdf'));

        unlink($tmpFile);
    }

    public function test_validate_file_content_rejects_fake_pdf(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, 'this is not a real pdf');

        $this->assertFalse(FileValidationService::validateFileContent($tmpFile, 'application/pdf'));

        unlink($tmpFile);
    }

    public function test_validate_file_content_validates_png(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, "\x89PNG\r\n\x1a\n" . 'fake png content');

        $this->assertTrue(FileValidationService::validateFileContent($tmpFile, 'image/png'));

        unlink($tmpFile);
    }

    public function test_validate_file_content_rejects_fake_png(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, 'not a png');

        $this->assertFalse(FileValidationService::validateFileContent($tmpFile, 'image/png'));

        unlink($tmpFile);
    }

    public function test_validate_file_content_validates_jpeg(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, "\xFF\xD8\xFF" . 'fake jpeg content');

        $this->assertTrue(FileValidationService::validateFileContent($tmpFile, 'image/jpeg'));

        unlink($tmpFile);
    }

    public function test_validate_file_content_validates_docx(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, "PK\x03\x04" . 'fake docx content');

        $this->assertTrue(FileValidationService::validateFileContent(
            $tmpFile,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ));

        unlink($tmpFile);
    }

    public function test_validate_file_content_validates_doc(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, "\xD0\xCF\x11\xE0" . 'fake doc content');

        $this->assertTrue(FileValidationService::validateFileContent($tmpFile, 'application/msword'));

        unlink($tmpFile);
    }
}
