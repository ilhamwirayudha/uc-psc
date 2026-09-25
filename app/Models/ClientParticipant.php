<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientParticipant extends Model
{
    protected $fillable = [
        'client_id',
        'nama_peserta',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
