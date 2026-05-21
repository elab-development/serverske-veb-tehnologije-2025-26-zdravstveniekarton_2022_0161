<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blood_type',
        'allergies',
        'chronic_diseases',
    ];

    /**
     * Veza: Zdravstveni karton pripada jednom korisniku (pacijentu)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}