<?php

namespace App\Models;

use App\Services\PublicMediaStorage;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'avatar', 'hero_banner', 'employee_id', 'position', 'phone', 'gender', 'address'])]
#[Hidden(['password', 'remember_token'])]
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

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'created_by');
    }


    public function assignedPairings(): HasMany
    {
        return $this->hasMany(Pairing::class, 'assigned_by');
    }

    public function administeredTests(): HasMany
    {
        return $this->hasMany(TestResult::class, 'administered_by');
    }

    public function assignedClients(): HasMany
    {
        return $this->hasMany(Client::class, 'assigned_staff_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class)->orderByDesc('created_at')->orderByDesc('id');
    }
}
