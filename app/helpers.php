<?php

function resultado_final($codigo_modalidad, $nota_recuperacion_1, $nota_recuperacion_2, $nota_promedio_final){
    $resultado_por_asignatura = array('A','0');
    /// VALIDAR PRIMERO A QUE MODALIDAD PERTENECE
        // 03 -> PRIMER CICLO
        // 04 -> SEGUNDO CICLO
        // 05 -> TERCER CICLO
        // 06 -> BACHILLERATO GENERAL
        // 07 -> BACHILLERATO TECNICO
        // 08 -> BACHILLERATO TECNICO VOCACIONAL SECRETARIADO
        // 09 -> BACHILLERATO TECNICO VOCACIONAL CONTADUR
        // 10 -> TERCER CICLO NOCTURNA
        // 11 -> BACHILLERATO GENERAL NOCTURNA
        // 12 -> EDUCACION BASDICA DE ADULTOS NOCTURNA
        switch ($codigo_modalidad) {
            case ($codigo_modalidad >= '03' && $codigo_modalidad <= '05'):
                if($nota_recuperacion_1 <> 0 ){
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_1) / 2,0);
                }elseif ($nota_recuperacion_2 <> 0) {
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_2) / 2,0);
                }
                // RESUTLADO Y ENVIAR
                    if($nota_promedio_final < 5){$resultado_por_asignatura[0] ="R";}
            break;
            case ($codigo_modalidad >= '06' && $codigo_modalidad <= '09'):
                if($nota_recuperacion_1 <> 0 ){
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_1) / 2,0);
                }elseif ($nota_recuperacion_2 <> 0) {
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_2) / 2,0);
                }
                // RESUTLADO Y ENVIAR
                    if($nota_promedio_final < 6){$resultado_por_asignatura[0] ="R";}
            break;
            case ($codigo_modalidad >= '10' && $codigo_modalidad <= '12'):
                if($nota_recuperacion_1 <> 0 ){
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_1) / 2,0);
                }elseif ($nota_recuperacion_2 <> 0) {
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_2) / 2,0);
                }
                // RESUTLADO Y ENVIAR
                    if($nota_promedio_final < 5){$resultado_por_asignatura[0] ="R";}
            break;
            default:
                $resultado_por_asignatura[0] ="R";
                break;
        }
    return $resultado_por_asignatura;
    
}

function resultado_nota_final($codigo_modalidad, $nota_recuperacion_1, $nota_recuperacion_2, $nota_promedio_final){
    $resultado_nota = 0;

    return $resultado_nota;
    
}