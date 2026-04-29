<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LockerAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rfid_card_id',
        'locker_id',
        'accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'accessed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'user_id');
    }

    public function rfidCard(): BelongsTo
    {
        return $this->belongsTo(RfidCard::class);
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(Locker::class);
    }
}
