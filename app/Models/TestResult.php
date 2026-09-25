<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestResult extends Model
{
    // Lifecycle Status Constants
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_TEST_COMPLETED = 'test_completed';
    public const STATUS_WAITING_ASSESSMENT = 'waiting_assessment';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_REVISION = 'revision';
    public const STATUS_REVIEW_COMPLETED = 'review_completed';
    public const STATUS_RESULT_READY = 'result_ready';
    public const STATUS_RESULT_SENT = 'result_sent';
    public const STATUS_COMPLETED = 'completed';

    public const STATUS_LABELS = [
        self::STATUS_SCHEDULED => 'Terjadwal',
        self::STATUS_TEST_COMPLETED => 'Selesai Tes',
        self::STATUS_WAITING_ASSESSMENT => 'Menunggu Penilaian',
        self::STATUS_ASSIGNED => 'Sudah Diassign',
        self::STATUS_IN_REVIEW => 'Sedang Dinilai',
        self::STATUS_REVISION => 'Perlu Revisi',
        self::STATUS_REVIEW_COMPLETED => 'Review Selesai',
        self::STATUS_RESULT_READY => 'Hasil Siap Dikirim',
        self::STATUS_RESULT_SENT => 'Hasil Dikirim',
        self::STATUS_COMPLETED => 'Selesai',
    ];

    // Execution Methods
    public const METHOD_OFFLINE = 'offline';
    public const METHOD_ONLINE = 'online';

    public const METHOD_LABELS = [
        self::METHOD_OFFLINE => 'Offline',
        self::METHOD_ONLINE => 'Online',
    ];

    // Delivery Methods
    public const DELIVERY_WHATSAPP = 'whatsapp';
    public const DELIVERY_EMAIL = 'email';
    public const DELIVERY_OFFLINE = 'offline';

    public const DELIVERY_LABELS = [
        self::DELIVERY_WHATSAPP => 'WhatsApp',
        self::DELIVERY_EMAIL => 'Email',
        self::DELIVERY_OFFLINE => 'Offline / Langsung',
    ];

    protected $fillable = [
        'client_id',
        'booking_id',
        'test_name',
        'result_summary',
        'file_path',
        'administered_by',
        'tested_at',
        'method',
        'status',
        'assigned_at',
        'reviewed_at',
        'completed_at',
        'result_due_date',
        'delivery_method',
        'delivered_at',
        'delivered_by',
        'revision_notes',
    ];

    protected function casts(): array
    {
        return [
            'tested_at' => 'date',
            'result_due_date' => 'date',
            'assigned_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administered_by');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TestResultDocument::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TestResultActivity::class)->orderBy('created_at', 'desc');
    }

    // --- Accessors & Attributes ---

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status ?? ''));
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHOD_LABELS[$this->method] ?? ucfirst($this->method ?? 'offline');
    }

    public function getDeliveryMethodLabelAttribute(): ?string
    {
        return $this->delivery_method ? (self::DELIVERY_LABELS[$this->delivery_method] ?? ucfirst($this->delivery_method)) : null;
    }

    public function getStaffPengujiAttribute(): ?User
    {
        return $this->booking?->staffPenguji;
    }

    public function getStaffKoreksiAttribute(): ?User
    {
        return $this->booking?->staffKoreksi;
    }

    public function getStaffPelaporAttribute(): ?User
    {
        return $this->booking?->staffPelapor;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'bg-gray-100 text-gray-800 border-gray-200',
            self::STATUS_TEST_COMPLETED => 'bg-blue-50 text-blue-700 border-blue-200',
            self::STATUS_WAITING_ASSESSMENT => 'bg-yellow-50 text-yellow-800 border-yellow-200',
            self::STATUS_ASSIGNED => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::STATUS_IN_REVIEW => 'bg-yellow-50 text-yellow-800 border-yellow-200',
            self::STATUS_REVISION => 'bg-yellow-50 text-yellow-800 border-yellow-200',
            self::STATUS_REVIEW_COMPLETED => 'bg-teal-50 text-teal-700 border-teal-200',
            self::STATUS_RESULT_READY => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::STATUS_RESULT_SENT => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            self::STATUS_COMPLETED => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    // --- SLA Workday Calculations ---

    /**
     * Menghitung target tanggal hasil berdasarkan jumlah hari kerja (Senin - Jumat).
     */
    public static function calculateWorkDueDate(?Carbon $startDate, int $workDays = 7): Carbon
    {
        $date = $startDate ? $startDate->copy() : Carbon::today();
        $added = 0;

        while ($added < $workDays) {
            $date->addDay();
            // 1 = Monday, 5 = Friday
            if ($date->isWeekday()) {
                $added++;
            }
        }

        return $date;
    }

    /**
     * Menghitung sisa hari kerja sampai result_due_date.
     * Jika negatif, berarti terlambat sebanyak X hari kerja.
     */
    public function getRemainingWorkDaysAttribute(): ?int
    {
        if (!$this->result_due_date) {
            return null;
        }

        $today = Carbon::today();
        $target = $this->result_due_date->copy()->startOfDay();

        if ($today->equalTo($target)) {
            return 0;
        }

        $isOverdue = $today->greaterThan($target);
        $start = $isOverdue ? $target : $today;
        $end = $isOverdue ? $today : $target;

        $workDays = 0;
        $curr = $start->copy()->addDay();
        while ($curr->lessThanOrEqualTo($end)) {
            if ($curr->isWeekday()) {
                $workDays++;
            }
            $curr->addDay();
        }

        return $isOverdue ? -$workDays : $workDays;
    }

    public function getIsOverdueAttribute(): bool
    {
        if (in_array($this->status, [self::STATUS_RESULT_SENT, self::STATUS_COMPLETED])) {
            return false;
        }

        if (!$this->result_due_date) {
            return false;
        }

        return Carbon::today()->greaterThan($this->result_due_date);
    }

    public function getSlaBadgeAttribute(): array
    {
        if (!$this->result_due_date) {
            return [
                'label' => 'Tanpa Target',
                'class' => 'bg-gray-100 text-gray-600 border-gray-200',
                'is_overdue' => false,
                'days' => null,
            ];
        }

        if (in_array($this->status, [self::STATUS_RESULT_SENT, self::STATUS_COMPLETED])) {
            return [
                'label' => 'Tuntas',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'is_overdue' => false,
                'days' => 0,
            ];
        }

        $days = $this->remaining_work_days;

        if ($this->is_overdue) {
            return [
                'label' => 'Terlambat ' . abs($days) . ' hari kerja',
                'class' => 'bg-rose-100 text-rose-700 border-rose-300 font-bold',
                'is_overdue' => true,
                'days' => $days,
            ];
        }

        if ($days === 0) {
            return [
                'label' => 'Batas Hari Ini',
                'class' => 'bg-amber-100 text-amber-800 border-amber-300 font-bold',
                'is_overdue' => false,
                'days' => 0,
            ];
        }

        if ($days <= 2) {
            return [
                'label' => 'Sisa ' . $days . ' hari kerja',
                'class' => 'bg-amber-50 text-amber-700 border-amber-200 font-semibold',
                'is_overdue' => false,
                'days' => $days,
            ];
        }

        return [
            'label' => 'Sisa ' . $days . ' hari kerja',
            'class' => 'bg-purple-50 text-purple-700 border-purple-200',
            'is_overdue' => false,
            'days' => $days,
        ];
    }

    // --- State Transition Validations ---

    public function canTransitionTo(string $nextStatus): bool
    {
        if ($this->status === $nextStatus) {
            return false;
        }

        $allowedTransitions = [
            self::STATUS_SCHEDULED => [self::STATUS_TEST_COMPLETED],
            self::STATUS_TEST_COMPLETED => [self::STATUS_WAITING_ASSESSMENT, self::STATUS_ASSIGNED],
            self::STATUS_WAITING_ASSESSMENT => [self::STATUS_ASSIGNED, self::STATUS_IN_REVIEW],
            self::STATUS_ASSIGNED => [self::STATUS_IN_REVIEW],
            self::STATUS_IN_REVIEW => [self::STATUS_REVIEW_COMPLETED, self::STATUS_REVISION],
            self::STATUS_REVISION => [self::STATUS_IN_REVIEW, self::STATUS_REVIEW_COMPLETED],
            self::STATUS_REVIEW_COMPLETED => [self::STATUS_RESULT_READY, self::STATUS_REVISION],
            self::STATUS_RESULT_READY => [self::STATUS_RESULT_SENT, self::STATUS_REVISION],
            self::STATUS_RESULT_SENT => [self::STATUS_COMPLETED],
            self::STATUS_COMPLETED => [],
        ];

        // Admin boleh melakukan transisi fleksibel jika diperlukan
        if (auth()->check() && auth()->user()->isAdmin()) {
            return true;
        }

        return in_array($nextStatus, $allowedTransitions[$this->status] ?? []);
    }

    // --- Activity Logger Helper ---

    public function logActivity(string $action, string $description, array $properties = [], ?int $userId = null): TestResultActivity
    {
        return $this->activities()->create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'properties' => !empty($properties) ? $properties : null,
            'created_at' => now(),
        ]);
    }
}
