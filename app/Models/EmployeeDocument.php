<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'file_name',
        'storage_path',
        'mime_type',
        'size_bytes',
        'label',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'size_bytes' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public function getFileIconAttribute(): string
    {
        return match(true) {
            str_contains($this->mime_type, 'pdf') => '📄',
            str_contains($this->mime_type, 'image') => '🖼',
            str_contains($this->mime_type, 'word') || str_contains($this->mime_type, 'document') => '📝',
            default => '📎',
        };
    }
}
