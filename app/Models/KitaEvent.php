<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitaEvent extends Model
{
    use HasFactory;

    const TYPE_SCHLIESSTAG  = 'SCHLIESSTAG';
    const TYPE_KURZE_ZEITEN = 'KURZE_ZEITEN';
    const TYPE_FORTBILDUNG  = 'FORTBILDUNG';
    const TYPE_INFO         = 'INFO';

    protected $fillable = [
        'kita_id',
        'date',
        'end_date',
        'event_type',
        'title',
        'description',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'date'     => 'date',
        'end_date' => 'date',
    ];

    public function kita()
    {
        return $this->belongsTo(Kita::class);
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_SCHLIESSTAG  => 'Schließtag',
            self::TYPE_KURZE_ZEITEN => 'Verkürzte Öffnungszeiten',
            self::TYPE_FORTBILDUNG  => 'Fortbildung / Ausfall',
            self::TYPE_INFO         => 'Info / Veranstaltung',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[$this->event_type] ?? $this->event_type;
    }
}
