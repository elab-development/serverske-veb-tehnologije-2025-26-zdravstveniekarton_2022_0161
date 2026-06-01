<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'blood_type',
        'allergies',
        'chronic_conditions',
        'current_medications',
        'emergency_contact_name',
        'emergency_contact_phone',
        'insurance_number',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * @return HasMany<MedicalVisit, $this>
     */
    public function medicalVisits(): HasMany
    {
        return $this->hasMany(MedicalVisit::class);
    }
}
