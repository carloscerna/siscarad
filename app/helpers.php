<?php

function resultado_final($codigo_modalidad, $nota_recuperacion_1, $nota_recuperacion_2, $nota_promedio_final){
    $resultado_por_asignatura = array('A','0');
    /// VALIDAR PRIMERO A QUE MODALIDAD PERTENECE
        // 03 -> PRIMER CICLO
        // 04 -> SEGUNDO CICLO
        // 05 -> TERCER CICLO
        switch ($codigo_modalidad) {
            case '03':
                if($nota_recuperacion_1 <> 0 ){
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_1) / 2,0);
                }elseif ($nota_recuperacion_2 <> 0) {
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_2) / 2,0);
                }
                // RESUTLADO Y ENVIAR
                    if($nota_promedio_final < 5){$resultado_por_asignatura[0] ="R";}
            break;
            case '03':
            # code...
            break;
            case '03':
            # code...
            break;
            default:
                # code...
                break;
        }
    return $resultado_por_asignatura;
    
}

function resultado_nota_final($codigo_modalidad, $nota_recuperacion_1, $nota_recuperacion_2, $nota_promedio_final){
    $resultado_nota = 0;

    return $resultado_nota;
    
}