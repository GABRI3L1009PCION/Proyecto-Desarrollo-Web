<?php
// app/Models/Enrollment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $table = 'enrollments';
    protected $fillable = ['student_id','offering_id','estado'];

    public function student()  { return $this->belongsTo(Student::class); }
    public function offering() { return $this->belongsTo(Offering::class); }
}
