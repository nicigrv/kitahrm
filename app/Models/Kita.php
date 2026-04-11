<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kita extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_code',
        'address',
        'phone',
        'email',
        'min_first_aid',
        'min_staff_total',
        'min_skilled_staff',
        'target_weekly_hours',
        'notes',
    ];

    protected $casts = [
        'min_first_aid'       => 'integer',
        'min_staff_total'     => 'integer',
        'min_skilled_staff'   => 'integer',
        'target_weekly_hours' => 'decimal:1',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function trainingRequirements()
    {
        return $this->hasMany(KitaTrainingRequirement::class);
    }

    public function closingDays()
    {
        return $this->hasMany(KitaClosingDay::class);
    }

    public function getActualWeeklyHoursAttribute(): float
    {
        return (float) $this->employees()->where('is_active', true)->sum('weekly_hours');
    }

    public function activeEmployees()
    {
        return $this->employees()->where('is_active', true);
    }

    public function getActiveFirstAidCountAttribute(): int
    {
        $firstAidCategories = TrainingCategory::where('is_first_aid', true)->where('is_active', true)->pluck('id');

        if ($firstAidCategories->isEmpty()) {
            return 0;
        }

        $count = 0;
        $activeEmployees = $this->employees()->where('is_active', true)->get();

        foreach ($activeEmployees as $employee) {
            $hasValid = TrainingCompletion::where('employee_id', $employee->id)
                ->whereIn('category_id', $firstAidCategories)
                ->where(function ($q) {
                    $q->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>', now());
                })
                ->exists();

            if ($hasValid) {
                $count++;
            }
        }

        return $count;
    }

    public function hasFirstAidCoverage(): bool
    {
        return $this->active_first_aid_count >= $this->min_first_aid;
    }
}
