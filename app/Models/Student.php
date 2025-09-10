<?php
// app/Models/Student.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';
    protected $fillable = ['branch_id','nombres','email','telefono','fecha_nacimiento']; // ajusta

    public function branch()      { return $this->belongsTo(Branch::class); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
}
