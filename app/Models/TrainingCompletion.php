<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TrainingCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'category_id',
        'completed_date',
        'expiry_date',
        'notes',
        'certificate_path',
    ];

    protected $casts = [
        'completed_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function category()
    {
        return $this->belongsTo(TrainingCategory::class, 'category_id');
    }

    public function isExpired(): bool
    {
        if (is_null($this->expiry_date)) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 60): bool
    {
        if (is_null($this->expiry_date)) {
            return false;
        }
        if ($this->isExpired()) {
            return false;
        }
        return $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function expiryStatus(): string
    {
        if (is_null($this->expiry_date)) {
            return 'none';
        }
        if ($this->isExpired()) {
            return 'expired';
        }
        if ($this->isExpiringSoon(60)) {
            return 'expiring';
        }
        return 'valid';
    }
}
