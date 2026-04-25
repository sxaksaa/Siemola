<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'locker_id',
        'item_id',
        'borrowed_rfid_uid',
        'borrowed_at',
        'due_at',
        'returned_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(Locker::class);
    }
}
