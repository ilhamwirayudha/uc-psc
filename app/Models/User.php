<?php

namespace App\Models;

use App\Services\PublicMediaStorage;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password', 'role', 'counselor_id',
    'avatar', 'hero_banner', 'employee_id', 'position', 'phone', 'gender', 'address',
    'google_email', 'google_access_token', 'google_refresh_token',
    'google_token_expires_at', 'google_calendar_id', 'google_connected_at',
])]
#[Hidden(['password', 'remember_token', 'google_access_token', 'google_refresh_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get avatar url or null.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return PublicMediaStorage::url($this->avatar);
    }

    /**
     * Get hero banner url or null.
     */
    public function getHeroBannerUrlAttribute(): ?string
    {
        return PublicMediaStorage::url($this->hero_banner);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google_access_token' => 'encrypted',
            'google_refresh_token' => 'encrypted',
            'google_token_expires_at' => 'datetime',
            'google_connected_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isPsikolog(): bool
    {
        return $this->role === 'psikolog';
    }

    public function hasGoogleCalendarConnected(): bool
    {
        return !empty($this->google_connected_at)
            && (!empty($this->google_refresh_token) || !empty($this->google_access_token));
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(Counselor::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'created_by');
    }

    public function assignedPairings(): HasMany
    {
        return $this->hasMany(Pairing::class, 'assigned_by');
    }

    public function bookingAssignees(): HasMany
    {
        return $this->hasMany(BookingAssignee::class);
    }

    public function googleCalendarEvents(): HasMany
    {
        return $this->hasMany(GoogleCalendarEvent::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class)->orderByDesc('created_at')->orderByDesc('id');
    }
}
