<?php
// app/Models/Course.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';
    protected $fillable = ['nombre','descripcion','horas','estado'];

    public function offerings() { return $this->hasMany(Offering::class); }
}
