<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Locker extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'location',
        'device_id',
        'status',
        'last_ping_at',
    ];

    protected function casts(): array
    {
        return [
            'last_ping_at' => 'datetime',
        ];
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }
}
