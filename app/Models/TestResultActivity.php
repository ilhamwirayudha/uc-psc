<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResultActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'test_result_id',
        'user_id',
        'action',
        'description',
        'properties',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function testResult(): BelongsTo
    {
        return $this->belongsTo(TestResult::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
