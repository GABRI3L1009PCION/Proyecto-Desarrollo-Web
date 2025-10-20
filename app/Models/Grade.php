<?php
// app/Models/Grade.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $table = 'grades';

    protected $fillable = [
        'enrollment_id',
        'parcial1',
        'parcial2',
        'final',
        'total',         // 🔹 Nueva columna
        'estado',        // 🔹 Nueva columna
        'observaciones',
    ];

    // 🔹 Relación con inscripción (cada nota pertenece a una inscripción)
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
}
