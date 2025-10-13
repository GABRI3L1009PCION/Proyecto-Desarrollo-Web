<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'user_id',
        'branch_id',
        'nombres',
        'telefono',
    ];

    // === Relaciones ===

    // 🔹 Un catedrático pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 Un catedrático pertenece a una sucursal
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // 🔹 Un catedrático puede tener muchas asignaciones (offerings)
    public function offerings()
    {
        return $this->hasMany(Offering::class, 'teacher_id');
    }

    // === Seguridad al eliminar ===
    /**
     * 🚫 Evita eliminar un catedrático con asignaciones o alumnos inscritos.
     */
    protected static function booted()
    {
        static::deleting(function ($teacher) {
            // Cargar todas las asignaciones y sus inscripciones
            $teacher->load('offerings.enrollments');

            $asignaciones = $teacher->offerings->count();
            $alumnos = $teacher->offerings->sum(fn($o) => $o->enrollments->count());

            // Si tiene asignaciones o alumnos, impedir la eliminación
            if ($asignaciones > 0) {
                $mensaje = $alumnos > 0
                    ? "No se puede eliminar este catedrático porque tiene asignaciones con alumnos inscritos ({$alumnos} en total)."
                    : "No se puede eliminar este catedrático porque tiene asignaciones activas. Elimine o reasigne sus asignaciones antes de proceder.";

                throw new \Exception($mensaje);
            }
        });
    }
}
