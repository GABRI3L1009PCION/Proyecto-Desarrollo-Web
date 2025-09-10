<?php
// app/Models/Offering.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offering extends Model
{
    protected $table = 'offerings';
    protected $fillable = ['course_id','branch_id','teacher_id','periodo','horario','cupo','estado'];

    public function course()     { return $this->belongsTo(Course::class); }
    public function branch()     { return $this->belongsTo(Branch::class); }
    public function teacher()    { return $this->belongsTo(Teacher::class); }
    public function enrollments(){ return $this->hasMany(Enrollment::class); }
}
