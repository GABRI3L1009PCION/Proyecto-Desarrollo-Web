<?php
// app/Models/Course.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'codigo',
        'nombre',
        'creditos',
        'descripcion',
    ];

    // Relación: un curso puede tener muchas ofertas
    public function offerings()
    {
        return $this->hasMany(Offering::class);
    }
}
