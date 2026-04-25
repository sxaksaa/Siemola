<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nim',
        'rfid_uid',
        'study_program',
        'class_name',
        'status',
        'phone',
        'last_tapped_at',
    ];

    protected function casts(): array
    {
        return [
            'last_tapped_at' => 'datetime',
        ];
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }
}
