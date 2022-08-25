<?php

function EncabezadoCatalogoAreaAsignatura($catalogo_area_asignatura_codigo, $codigo_area){
    global $catalogo_area_asignatura_area, $catalogo_area_basica;
    // LINEA DE DIVISIÓN - PARA EL ÁREA BÁSICA.
    if($catalogo_area_asignatura_codigo[0] == $codigo_area){
        if($catalogo_area_basica == true){
                $catalogo_area_nombre = $catalogo_area_asignatura_area;
            $catalogo_area_basica = false;
        }
    }
    return $catalogo_area_nombre;
}