<?php
// app/Models/Teacher.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'teachers';
    protected $fillable = ['branch_id','nombres','email','telefono']; // ajusta a tus columnas reales

    public function branch()    { return $this->belongsTo(Branch::class); }
    public function offerings() { return $this->hasMany(Offering::class); }
}
