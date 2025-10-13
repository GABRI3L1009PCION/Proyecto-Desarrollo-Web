<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Offering extends Model
{
    use HasFactory;

    protected $table = 'offerings';

    // 🔹 Campos que existen en la tabla y se pueden asignar en masa
    protected $fillable = [
        'course_id',
        'teacher_id',
        'branch_id',
        'grade',
        'level',
        'anio',
        'ciclo',
        'cupo',
        'horario',
    ];

    // === Relaciones ===

    // 📘 Curso asignado
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // 🏫 Sucursal correspondiente
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // 👨‍🏫 Catedrático que imparte la asignación
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // 🧑‍🎓 Inscripciones de alumnos en esta asignación
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'offering_id');
    }

    // === Seguridad al eliminar ===
    /**
     * 🚫 Evita eliminar una asignación si tiene alumnos inscritos.
     */
    protected static function booted()
    {
        static::deleting(function ($offering) {
            $alumnos = $offering->enrollments()->count();

            if ($alumnos > 0) {
                throw new \Exception(
                    "No se puede eliminar esta asignación porque tiene alumnos inscritos ({$alumnos})."
                );
            }
        });
    }
}
