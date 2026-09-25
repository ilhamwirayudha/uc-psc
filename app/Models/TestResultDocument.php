<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResultDocument extends Model
{
    public const TYPE_TEST_FORM = 'test_form';
    public const TYPE_ANSWER_SHEET = 'answer_sheet';
    public const TYPE_SCORING = 'scoring';
    public const TYPE_PSYCHOLOGICAL_REPORT = 'psychological_report';
    public const TYPE_SUPPORTING_DOCUMENT = 'supporting_document';
    public const TYPE_FINAL_RESULT = 'final_result';

    public const TYPES = [
        self::TYPE_TEST_FORM => 'Formulir / Lembar Tes',
        self::TYPE_ANSWER_SHEET => 'Lembar Jawaban / Scan Berkas',
        self::TYPE_SCORING => 'Skoring & Perhitungan',
        self::TYPE_PSYCHOLOGICAL_REPORT => 'Draf Laporan Psikologis',
        self::TYPE_SUPPORTING_DOCUMENT => 'Dokumen Pendukung',
        self::TYPE_FINAL_RESULT => 'Laporan Hasil Final',
    ];

    protected $fillable = [
        'test_result_id',
        'document_type',
        'original_name',
        'file_path',
        'file_size',
        'uploaded_by',
    ];

    public function testResult(): BelongsTo
    {
        return $this->belongsTo(TestResult::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return self::TYPES[$this->document_type] ?? 'Dokumen';
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes >= 1024 && $i < 3; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
