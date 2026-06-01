<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_PATIENT = 'patient';

    public const ROLE_NURSE = 'nurse';

    public const ROLE_DOCTOR = 'doctor';

    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasOne<MedicalRecord, $this>
     */
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'patient_id');
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function patientAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function doctorAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function nurseAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'nurse_id');
    }

    /**
     * @return HasMany<MedicalVisit, $this>
     */
    public function doctorMedicalVisits(): HasMany
    {
        return $this->hasMany(MedicalVisit::class, 'doctor_id');
    }

    /**
     * @return HasMany<MedicalVisit, $this>
     */
    public function nurseMedicalVisits(): HasMany
    {
        return $this->hasMany(MedicalVisit::class, 'nurse_id');
    }
}
