<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MedicalRecord extends Model {
 protected $fillable=['patient_id','doctor_id','diagnosis','therapy'];
}
