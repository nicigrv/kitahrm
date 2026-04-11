<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'validity_months',
        'is_first_aid',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'validity_months' => 'integer',
        'is_first_aid' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function completions()
    {
        return $this->hasMany(TrainingCompletion::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
