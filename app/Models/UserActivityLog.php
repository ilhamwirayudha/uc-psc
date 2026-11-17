<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'action',
        'title',
        'description',
        'meta',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Catat entri log aktivitas baru untuk pengguna tertentu.
     */
    public static function record(
        int $userId,
        string $category,
        string $action,
        string $title,
        string $description,
        ?string $meta = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'category' => $category,
            'action' => $action,
            'title' => $title,
            'description' => $description,
            'meta' => $meta,
        ]);
    }
}
