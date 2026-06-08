<?php

namespace App\Models;

use Database\Factories\MedicalVisitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MedicalVisit extends Model
{
    /** @use HasFactory<MedicalVisitFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINALIZED = 'finalized';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'medical_record_id',
        'appointment_id',
        'doctor_id',
        'nurse_id',
        'symptoms',
        'temperature',
        'blood_pressure',
        'heart_rate',
        'diagnosis',
        'therapy',
        'prescription',
        'doctor_note',
        'nurse_note',
        'follow_up_at',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'follow_up_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<MedicalRecord, $this>
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }
}
