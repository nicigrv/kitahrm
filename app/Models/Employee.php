<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Model
{
    use HasFactory;

    const CONTRACT_UNBEFRISTET = 'UNBEFRISTET';
    const CONTRACT_BEFRISTET = 'BEFRISTET';
    const CONTRACT_MINIJOB = 'MINIJOB';
    const CONTRACT_AUSBILDUNG = 'AUSBILDUNG';
    const CONTRACT_PRAKTIKUM = 'PRAKTIKUM';
    const CONTRACT_ELTERNZEIT = 'ELTERNZEIT';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'birth_date',
        'position',
        'start_date',
        'end_date',
        'contract_type',
        'weekly_hours',
        'is_active',
        'notes',
        'kita_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'weekly_hours' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    public function kita()
    {
        return $this->belongsTo(Kita::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function trainingCompletions()
    {
        return $this->hasMany(TrainingCompletion::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getContractTypeLabelAttribute(): string
    {
        return match($this->contract_type) {
            self::CONTRACT_UNBEFRISTET => 'Unbefristet',
            self::CONTRACT_BEFRISTET => 'Befristet',
            self::CONTRACT_MINIJOB => 'Minijob',
            self::CONTRACT_AUSBILDUNG => 'Ausbildung',
            self::CONTRACT_PRAKTIKUM => 'Praktikum',
            self::CONTRACT_ELTERNZEIT => 'Elternzeit',
            default => $this->contract_type,
        };
    }

    public static function contractTypeOptions(): array
    {
        return [
            self::CONTRACT_UNBEFRISTET => 'Unbefristet',
            self::CONTRACT_BEFRISTET => 'Befristet',
            self::CONTRACT_MINIJOB => 'Minijob',
            self::CONTRACT_AUSBILDUNG => 'Ausbildung',
            self::CONTRACT_PRAKTIKUM => 'Praktikum',
            self::CONTRACT_ELTERNZEIT => 'Elternzeit',
        ];
    }

    public function hasValidFirstAid(): bool
    {
        $firstAidCategories = TrainingCategory::where('is_first_aid', true)->where('is_active', true)->pluck('id');

        if ($firstAidCategories->isEmpty()) {
            return false;
        }

        return TrainingCompletion::where('employee_id', $this->id)
            ->whereIn('category_id', $firstAidCategories)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
            })
            ->exists();
    }
}
