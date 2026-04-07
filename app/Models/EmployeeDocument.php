<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'uploaded_by',
        'document_type',
        'path',
        'original_name',
        'mime_type',
        'size',
        'parsed_text',
        'status',
        'analysis_result',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'analysis_result' => 'array',
            'processed_at' => 'datetime',
            'status' => DocumentStatus::class,
            'document_type' => DocumentType::class,
        ];
    }

    // Legacy constants — use DocumentStatus and DocumentType enums instead
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PARSED = 'parsed';
    const STATUS_FAILED = 'failed';

    const TYPE_CONTRACT = 'contract';
    const TYPE_DIPLOMA = 'diploma';
    const TYPE_CERTIFICATE = 'certificate';
    const TYPE_ID_DOCUMENT = 'id_document';
    const TYPE_MEDICAL = 'medical';
    const TYPE_OTHER = 'other';

    // ========== Relationships ==========

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ========== Scopes ==========

    public function scopePending($query)
    {
        return $query->where('status', DocumentStatus::Pending);
    }

    public function scopeParsed($query)
    {
        return $query->where('status', DocumentStatus::Parsed);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', DocumentStatus::Failed);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeForEmployee($query, int $employeeProfileId)
    {
        return $query->where('employee_profile_id', $employeeProfileId);
    }

    // ========== Accessors ==========

    public function getDocumentTypeLabelAttribute(): string
    {
        return $this->document_type?->label() ?? 'Документ';
    }

    public function getDocumentTypeIconAttribute(): string
    {
        return $this->document_type?->icon() ?? 'bi-file-earmark';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? 'Неизвестно';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'secondary';
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' МБ';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' КБ';
        return $bytes . ' Б';
    }

    public function getIsParsedAttribute(): bool
    {
        return $this->status === DocumentStatus::Parsed;
    }

    public function getHasAnalysisAttribute(): bool
    {
        return !empty($this->analysis_result);
    }

    // ========== Helpers ==========

    public function markAsProcessing(): bool
    {
        return $this->update(['status' => DocumentStatus::Processing]);
    }

    public function markAsParsed(string $text, ?array $analysisResult = null): bool
    {
        return $this->update([
            'parsed_text' => $text,
            'status' => DocumentStatus::Parsed,
            'analysis_result' => $analysisResult,
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $error): bool
    {
        return $this->update([
            'status' => DocumentStatus::Failed,
            'error_message' => $error,
            'processed_at' => now(),
        ]);
    }

    public function getContents(): ?string
    {
        $fullPath = $this->getFullPath();
        return file_exists($fullPath) ? file_get_contents($fullPath) : null;
    }

    public function getBase64Contents(): ?string
    {
        $contents = $this->getContents();
        return $contents ? base64_encode($contents) : null;
    }

    public function getFullPath(): string
    {
        if (str_starts_with($this->path, 'public/')) {
            return storage_path('app/' . $this->path);
        }
        return storage_path('app/public/' . $this->path);
    }

    public static function getAllowedExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'txt', 'rtf', 'jpg', 'jpeg', 'png'];
    }

    public static function getMaxSizeBytes(): int
    {
        return 10 * 1024 * 1024;
    }
}
