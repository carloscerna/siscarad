<?php
/*
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


 ////////////////////////////////////////////////////////////////////
            //////// crear matriz para la tabla CATALOGO_AREA_ASIGNATURA.
            //////////////////////////////////////////////////////////////////
            $catalogo_area_asignatura_codigo = array();	// matriz para los diferentes código y descripción.
            $catalogo_area_asignatura_area = array();
            $catalogo_area_basica = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_formativa = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_tecnica = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_edps = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_edecr = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_edre = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_complementaria = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_cc = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_alertas = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
        
            // CATALOGO ASIGNATURA
            //
            $CatalogoAreaAsignatura = DB::table('catalogo_area_asignatura')
            ->select('codigo','descripcion')
            ->get();
            foreach($CatalogoAreaAsignatura as $response_area){  //Llenar el arreglo con datos
                $catalogo_area_asignatura_codigo[] = (trim($response_area->codigo));
                $catalogo_area_asignatura_area[] = (trim($response_area->descripcion));
                //* incrementar valor de la fila para la array asociativa
            } // FIN DEL FOREACH para los datos de la insitucion.
*/
        function EncabezadoCatalogoAreaAsignatura($codigo_area){
            global $catalogo_area_basica, $catalogo_area_asignatura_codigo, $catalogo_area_asignatura_area;
        // LINEA DE DIVISIÓN - PARA EL ÁREA BÁSICA.
        if($catalogo_area_asignatura_codigo[0] == $codigo_area){
            if($catalogo_area_basica == true){
                    $catalogo_area_nombre = $catalogo_area_asignatura_area;
                $catalogo_area_basica = false;
            }
        }
        return $catalogo_area_nombre;
}