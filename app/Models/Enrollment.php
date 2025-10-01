<?php
// app/Models/Enrollment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';

    protected $fillable = [
        'student_id',
        'offering_id',
        'status',
        'fecha',
    ];

    // Relación con estudiante
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relación con oferta (curso+docente+sede)
    public function offering()
    {
        return $this->belongsTo(Offering::class);
    }
}
