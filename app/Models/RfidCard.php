<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfidCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'user_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'user_id');
    }

    public function lockerAccesses(): HasMany
    {
        return $this->hasMany(LockerAccess::class);
    }
}
