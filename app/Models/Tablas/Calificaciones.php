<?php

namespace App\Models\Tablas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificaciones extends Model
{
    use HasFactory;
    protected $table = "nota";
    protected $fillable = ['nota_a1_1','nota_a1_2','nota_a1_3','nota_p_p_1'];
}
