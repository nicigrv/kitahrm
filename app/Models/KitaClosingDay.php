<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitaClosingDay extends Model
{
    protected $fillable = ['kita_id', 'date', 'label'];

    protected $casts = ['date' => 'date'];

    public function kita()
    {
        return $this->belongsTo(Kita::class);
    }
}
