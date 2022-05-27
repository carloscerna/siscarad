<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
//Spatie
use Spatie\Permission\Models\Permission;

class SeederTablaPermisos extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $permisos = [
            //tabla roles
           /* 'ver-rol',
            'crear-rol',
            'editar-rol',
            'borrar-rol',
            //tabla asignatura
            'ver-asignatura',
            'crear-asignatura',
            'editar-asignatura',
            'borrar-asignatura',*/
            //tabla usuarios
            'ver-usuario',
            'crear-usuario',
            'editar-usuario',
            'borrar-usuario',
            //tabla calificaciones ( notas)
            'ver-calificacion-por-docente',
            'crear-calificacion-por-docente',
            'editar-calificacion-por-docente',
            'borrar-calificacion-por-docente',
            //tabla anecdotica
            'ver-anecdotica',
            'crear-anecdotica',
            'editar-anecdotica',
            'borrar-anecdotica',
        ];
    
        foreach ($permisos as $permiso) {
            Permission::create(['name'=>$permiso]);
        }
    }
}
