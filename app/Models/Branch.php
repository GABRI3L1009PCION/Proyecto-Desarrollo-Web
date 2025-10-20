<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Branch extends Model
{
    use HasFactory;

    // No es obligatorio, pero explícito
    protected $table = 'branches';

    protected $fillable = ['nombre', 'direccion', 'telefono'];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    /** Alumnos de la sucursal */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /** Docentes asignados a la sucursal (si tu tabla teachers tiene branch_id) */
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    /** Ofertas (grupos/convocatorias) que se dictan en la sucursal */
    public function offerings(): HasMany
    {
        return $this->hasMany(Offering::class);
    }

    /** Cursos que se ofrecen en la sucursal (vía offerings) */
    public function courses(): HasManyThrough
    {
        return $this->hasManyThrough(
            Course::class,     // Modelo destino
            Offering::class,   // Modelo intermedio
            'branch_id',       // FK en offerings hacia branches
            'id',              // PK en courses
            'id',              // PK en branches
            'course_id'        // FK en offerings hacia courses
        );
    }

    /** Inscripciones registradas en la sucursal (vía offerings) */
    public function enrollments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Enrollment::class, // destino
            Offering::class,   // intermedio
            'branch_id',       // FK en offerings -> branches
            'offering_id',     // FK en enrollments -> offerings
            'id',              // PK en branches
            'id'               // PK en offerings
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes útiles
    |--------------------------------------------------------------------------
    */

    /** Agrega conteos típicos para listados/tablitas del panel */
    public function scopeWithMetrics($query)
    {
        return $query->withCount(['students', 'offerings']);
    }
}
