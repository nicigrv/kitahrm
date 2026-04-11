<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitaTrainingRequirement extends Model
{
    protected $fillable = ['kita_id', 'category_id', 'min_count'];

    protected $casts = ['min_count' => 'integer'];

    public function kita()
    {
        return $this->belongsTo(Kita::class);
    }

    public function category()
    {
        return $this->belongsTo(TrainingCategory::class, 'category_id');
    }
}
