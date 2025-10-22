<?php
// funcion para los promedios finales de todos los niveles.
/**
 * Summary of resultado_final
 * @param mixed $codigo_modalidad
 * @param mixed $nota_recuperacion_1
 * @param mixed $nota_recuperacion_2
 * @param mixed $nota_promedio_final
 * @param mixed $codigo_area
 * @return array
 */
function resultado_final($codigo_modalidad, $nota_recuperacion_1, $nota_recuperacion_2, $nota_promedio_final, $codigo_area){
    $resultado_por_asignatura = ['A','0'];
	$EvaluarCalificacionFinal = 0;
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
            case ($codigo_modalidad >= '03' && $codigo_modalidad <= '05'): // EDUCACIÓN BÁSICA 4.º A 9º.
				$EvaluarCalificacionFinal = 5;
            break;
            case ($codigo_modalidad >= '06' && $codigo_modalidad <= '09'): // BACHILLERATO GENERAL.
				$EvaluarCalificacionFinal = 6;
            break;
			case ($codigo_modalidad == '21'): // BACHILLERATO GENERAL.
				$EvaluarCalificacionFinal = 6;
            break;
            case ($codigo_modalidad == '10'):	// TERCER CICLO NOCTURNA.
				$EvaluarCalificacionFinal = 5;
            break;
			case ($codigo_modalidad == '11'): // EDUCACIÓN MEDIA NOCTURNA
				$EvaluarCalificacionFinal = 6;
            break;
			case ($codigo_modalidad == '12'): // EDUCACIÓN BASÍCA DE ADULTOS NIVEL I,II Y III NOCTURNA
				$EvaluarCalificacionFinal = 5;
            break;
            case ($codigo_modalidad == '15'):	// EDUCACIÓN MEDIA BACHILLERATO TECNICO ADMINISTRATIVO CONTABLE.
				// VALIDAR CUANDO SEA EL AREA TECNICA POR MODULOS.
				if($codigo_area == '03'){
					$EvaluarCalificacionFinal = 3;
				}else{
					$EvaluarCalificacionFinal = 6;
				}
            break;
            default:
                $resultado_por_asignatura[0] ="R";
                break;
        }
		//////////////////////////////////////////////////////////////////////////////////////
		//	Fórmula.
		//////////////////////////////////////////////////////////////////////////////////////
			if($nota_recuperacion_2 == 0){
				if($nota_recuperacion_1 == 0){

				}else{
					$nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_1) / 2,0);	
				}
			}else{
				$nota_promedio_final = round(($nota_promedio_final + $nota_recuperacion_2) / 2,0);
			}
		// RESUTLADO Y ENVIAR
			if($nota_promedio_final < $EvaluarCalificacionFinal || $nota_promedio_final == 0){$resultado_por_asignatura[0] ="R";}
		//////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////
        // nota final con las recuperaciones.
            $resultado_por_asignatura[1] = $nota_promedio_final;
				return $resultado_por_asignatura;
}
// funcion que cambiar el resultado segun sea el nivel y la calificacion.
/**
 * Summary of resultado_concepto
 * @param mixed $codigo_modalidad
 * @param mixed $nota_promedio
 * @return string
 */
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
        case ($codigo_modalidad >= '06' && $codigo_modalidad <= '09' || $codigo_modalidad == "15"):
            if($nota_promedio >= 5 && $nota_promedio <= 6 ){
                $resultado_concepto = "B";
            }elseif ($nota_promedio >= 7 && $nota_promedio <= 8 ){
                $resultado_concepto = "MB";
            }elseif ($nota_promedio >= 9 && $nota_promedio <= 10 ){
                $resultado_concepto = "E";
            }
        break;
        case ($codigo_modalidad >= '10' && $codigo_modalidad <= '12'):	// NOCTURNA
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
/**
 * Summary of calcular_sobreedad_escala
 * @param mixed $edad
 * @param mixed $grado
 * @return int
 */
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
////////////////////////////////////////////////////
//Convierte fecha de mysql a normal
////////////////////////////////////////////////////
function cambiaf_a_normal($fecha)
{
	if(empty($fecha)){$fecha = '2000-01-01';}
    $cad = preg_split('/ /',$fecha);
    $sub_cad = preg_split('/-/',$cad[0]);
    $fecha_formateada = $sub_cad[2].'/'.$sub_cad[1].'/'.$sub_cad[0];
    return $fecha_formateada;
}
/////////////////////////////////////////////////////////////////////////////////////////
//				**	conversor
/////////////////////////////////////////////////////////////////////////////////////////
/**
 * Summary of segundosToCadenaD
 * @param mixed $min
 * @param mixed $calculo_horas
 * @return float
 */
function segundosToCadenaD($min, $calculo_horas)
{
	// Base 5 u 8 horas.
		$min_x_dia = $calculo_horas * 60;
	// calculos
		$dias = floor($min/$min_x_dia);
		$horas = $min % $min_x_dia;
		$residuo_dias = $horas % $min_x_dia;
		$horas = floor($residuo_dias / 60);
		$residuo_minutos = $residuo_dias % 60;
		$minutos = $residuo_minutos;
			return $dias;
}

/**
 * Summary of segundosToCadenaH
 * @param mixed $min
 * @param mixed $calculo_horas
 * @return float
 */
function segundosToCadenaH($min, $calculo_horas)
{
	// Base 5 u 8 horas.
		$min_x_dia = $calculo_horas * 60;
	// calculos
		$dias = floor($min/$min_x_dia);
		$horas = $min % $min_x_dia;
		$residuo_dias = $horas % $min_x_dia;
		$horas = floor($residuo_dias/60);
		$residuo_minutos = $residuo_dias%60;
		$minutos = $residuo_minutos;
			return $horas;
}

/**
 * Summary of segundosToCadenaM
 * @param mixed $min
 * @param mixed $calculo_horas
 * @return int
 */
function segundosToCadenaM($min, $calculo_horas)
{
	// Base 5 u 8 horas.
	$min_x_dia = $calculo_horas * 60;
	// calculos
	$dias = floor($min/$min_x_dia);
	$horas = $min%$min_x_dia;
	$residuo_dias = $horas%$min_x_dia;
	$horas = floor($residuo_dias/60);
	$residuo_minutos = $residuo_dias%60;
	$minutos = $residuo_minutos;
		return $minutos;
}

function segundosToCadena($min, $calculo_horas, $formato)
{
	// Base 5 u 8 horas.
	$min_x_dia = $calculo_horas * 60;
	// calculos
		$cadena = '';
		$dias = floor($min/$min_x_dia);
		$horas = $min%$min_x_dia;
		$residuo_dias = $horas%$min_x_dia;
		$horas = floor($residuo_dias/60);
		$residuo_minutos = $residuo_dias%60;
		$minutos = $residuo_minutos;
		if($formato == 1){
			$cadena = $dias.'d'.$horas.'h'.$minutos.'m';
		}else{
			$cadena = $dias.' días '.$horas.' horas '.$minutos.' minutos';
		}
			return $cadena;
}

function segundosToCadenaHorasMinustos($min)
{
	$cadena = '';
	$dias = floor($min/300);
	$horas = $min%300;
	$residuo_dias = $horas%300;
	$horas = floor($residuo_dias/60);
	$residuo_minutos = $residuo_dias%60;
	$minutos = $residuo_minutos;
	$cadena = $horas.'h '.$minutos.'m';
		return $cadena;
}

function conversor_segundos($seg_ini) {
	// Convertir a segundos.
		$horas = floor($seg_ini/3600);
		$minutos = floor(($seg_ini-($horas*3600))/60);
		$segundos = $seg_ini-($horas*3600)-($minutos*60);
//echo $horas.?h:?.$minutos.?m:?.$segundos.?s';
}

function convertirTexto($Texto){
	$texto = mb_convert_encoding($Texto,"ISO-8859-1","UTF-8");
	return $texto;
}