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
                    
                    if($nota_promedio_final < 5){
                        if($nota_recuperacion_2 <> 0){
                            $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_2) / 2,0);
                        }
                    }
                }elseif ($nota_recuperacion_2 <> 0) {
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_2) / 2,0);
                }
                // RESUTLADO Y ENVIAR
                    if($nota_promedio_final < 5){$resultado_por_asignatura[0] ="R";}
            break;
            case ($codigo_modalidad >= '06' && $codigo_modalidad <= '09'):
                if($nota_recuperacion_1 <> 0 ){
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_1) / 2,0);

                        if($nota_promedio_final < 6){
                            if($nota_recuperacion_2 <> 0){
                                $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_2) / 2,0);
                            }
                        }
                }elseif ($nota_recuperacion_2 <> 0) {
                    $nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_2) / 2,0);
                }
                // RESUTLADO Y ENVIAR
                    if($nota_promedio_final < 6 || $nota_promedio_final == 0){$resultado_por_asignatura[0] ="R";}
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
        // nota final con las recuperaciones.
            $resultado_por_asignatura[1] = $nota_promedio_final;
    return $resultado_por_asignatura;
    
}

function resultado_concepto($codigo_modalidad, $nota_promedio){
    $resultado_concepto = "R";
    switch ($codigo_modalidad) {
        case ($codigo_modalidad >= '03' && $codigo_modalidad <= '05'):
            if($nota_promedio >= 5 && $nota_promedio <= 6 ){
                $resultado_concepto = "B";
            }elseif ($nota_promedio >= 7 && $nota_promedio <= 8 ){
                $resultado_concepto = "MB";
            }elseif ($nota_promedio >= 9 && $nota_promedio <= 10 ){
                $resultado_concepto = "E";
            }
        break;
        case ($codigo_modalidad >= '06' && $codigo_modalidad <= '09'):
            if($nota_promedio >= 5 && $nota_promedio <= 6 ){
                $resultado_concepto = "B";
            }elseif ($nota_promedio >= 7 && $nota_promedio <= 8 ){
                $resultado_concepto = "MB";
            }elseif ($nota_promedio >= 9 && $nota_promedio <= 10 ){
                $resultado_concepto = "E";
            }
        break;
        case ($codigo_modalidad >= '10' && $codigo_modalidad <= '12'):
            if($nota_promedio >= 5 && $nota_promedio <= 6 ){
                $resultado_concepto = "B";
            }elseif ($nota_promedio >= 7 && $nota_promedio <= 8 ){
                $resultado_concepto = "MB";
            }elseif ($nota_promedio >= 9 && $nota_promedio <= 10 ){
                $resultado_concepto = "E";
            }
        break;
        default:
            
            break;
    }
    return $resultado_concepto;
    
}

// ESCALA DE SOBREEDAD.
function calcular_sobreedad_escala($edad,$grado)
	{
		global $sobreedad_escala;
			$sobreedad_escala = 1;
		
		if($edad >= 8 && $grado == "01" ){	// 7
				if($edad == 8){

				}else if($edad == 9){
					$sobreedad_escala = 2;
				}else if($edad == 10){
					$sobreedad_escala = 3;
				}else{
					$sobreedad_escala = 4;
				}
			}
		
		if($edad >= 9 && $grado == "02" ){ // 8
			if($edad == 9){

			}else if($edad == 10){
				$sobreedad_escala = 2;
			}else if($edad == 11){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 10 && $grado == "03" ){ // 9
			if($edad == 10){

			}else if($edad == 11){
				$sobreedad_escala = 2;
			}else if($edad == 12){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 11 && $grado == "04" ){	// 10
			if($edad == 11){

			}else if($edad == 12){
				$sobreedad_escala = 2;
			}else if($edad == 13){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 12 && $grado == "05" ){	// 11
			if($edad == 12){

			}else if($edad == 13){
				$sobreedad_escala = 2;
			}else if($edad == 14){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 13 && $grado == "06" ){		// 12
			if($edad == 13){

			}else if($edad == 14){
				$sobreedad_escala = 2;
			}else if($edad == 15){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 14 && $grado == "07" ){	// 13
			if($edad == 14){

			}else if($edad == 15){
				$sobreedad_escala = 2;
			}else if($edad == 16){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 15 && $grado == "08" ){	// 14
			if($edad == 15){

			}else if($edad == 16){
				$sobreedad_escala = 2;
			}else if($edad == 17){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 16 && $grado == "09" ){	// 15
			if($edad == 16){

			}else if($edad == 17){
				$sobreedad_escala = 2;
			}else if($edad == 18){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}

    if($edad >= 17 && $grado == "10" ){	// 16
		if($edad == 17){

		}else if($edad == 18){
			$sobreedad_escala = 2;
		}else if($edad == 19){
			$sobreedad_escala = 3;
		}else{
			$sobreedad_escala = 4;
		}
	}		
		
		if($edad >= 18 && $grado == "11" ){	// 17
			if($edad == 18){

			}else if($edad == 19){
				$sobreedad_escala = 2;
			}else if($edad == 20){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}		

		if($edad >= 19 && $grado == "12" ){	// 18
			if($edad == 19){

			}else if($edad == 20){
				$sobreedad_escala = 2;
			}else if($edad == 21){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		return $sobreedad_escala;
	}