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

    // Un catedrático pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un catedrático pertenece a una sucursal
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Un catedrático puede tener muchas ofertas (secciones)
    public function offerings()
    {
        return $this->hasMany(Offering::class);
    }
}
