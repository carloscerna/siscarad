@extends('layouts.app')

@php
// llamada de valores
use Illuminate\Support\Facades;
    $correo_docente = Auth::user()->email;                                        
    $nombre_docente = Auth::user()->name;
    $codigo_personal = Auth::user()->codigo_personal;   
    $codigo_institucion = Auth::user()->codigo_institucion;                                                
@endphp

@section('content')
@role("Docente")
<section class="section">
    <div class="section-header">
        <h5 class="page__heading">{{$nombre_docente}} - Ingreso de Calificaciones</h5>
    </div>
    {{-- Este DIV estaba vacío, lo he eliminado para evitar un card en blanco --}}
</section>
@endrole

<div class="section-body">
    <div class="row">
        <div class="col-lg-12">
            
            {{-- ===== MEJORA 1: CARD PARA LOS FILTROS ===== --}}
            <div class="card">
                <div class="card border-secondary shadow-lg">
                    <strong><i class="fas fa-filter"></i> Panel de Selección</strong>
                </div>
                <div class="card-body">
                    {!! Form::hidden('codigo_personal', $codigo_personal,['id'=>'codigo_personal', 'class'=>'form-control']) !!}
                    {!! Form::hidden('codigo_institucion', $codigo_institucion,['id'=>'codigo_institucion', 'class'=>'form-control']) !!}
                    {!! Form::hidden('codigo_area', '00',['id'=>'codigo_area', 'class'=>'form-control']) !!}
                    
                    {{-- ===== MEJORA 2: GRID LAYOUT PARA FILTROS ===== --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="codigo_annlectivo"><i class="fas fa-calendar-alt"></i> Año Lectivo:</label>
                                {!! Form::select('codigo_annlectivo', ['placeholder'=>'Selecciona'] + $annlectivo, null, ['id' => 'codigo_annlectivo', 'onchange' => 'BuscarPorAnnLectivo(this.value)','class' => 'form-control form-control-resaltado']) !!}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="codigo_grado_seccion_turno"><i class="fas fa-school"></i> Grado-Sección-Turno:</label>
                                {!! Form::select('codigo_grado_seccion_turno', ['placeholder'=>'Selecciona'], null, ['id' => 'codigo_grado_seccion_turno','onchange' => 'BuscarPorGradoSeccionAsignaturas(this.value)', 'class' => 'form-control form-control-resaltado']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="codigo_asignatura"><i class="fas fa-book"></i> Asignatura:</label>
                                {!! Form::select('codigo_asignatura', ['placeholder'=>'Selecciona'], null, ['class' => 'form-control form-control-resaltado', 'id' => 'codigo_asignatura', 'onchange' => 'BuscarPorAsignatura(this.value)']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="codigo_periodo"><i class="fas fa-list-ol"></i> Período:</label>
                                {!! Form::select('codigo_periodo', ['00'=>'Seleccionar...','01'=>'Periodo 1','02'=>'Periodo 2','03'=>'Periodo 3'], null, ['id' => 'codigo_periodo','onchange' => 'BuscarPorPeriodo(this.value)', 'class' => 'form-control form-control-resaltado']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="codigo_actividad_porcentaje"><i class="fas fa-percentage"></i> Actividades (%):</label>
                                {!! Form::select('codigo_actividad_porcentaje', ['00'=>'Seleccionar...','01'=>'Actividad 1 (35%)','02'=>'Actividad 2 (35%)','03'=>'Examen o Prueba Objetiva (30%)','04'=>'Recuperación (10%)'], null, ['id' => 'codigo_actividad_porcentaje','onchange' => 'BuscarPorActividadPorcentaje(this.value)', 'class' => 'form-control form-control-resaltado']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    {{-- Lo dejamos vacío para que actúe solo como un separador visual --}}
                    &nbsp;
                </div>
            </div>

            {{-- ===== MEJORA 3: ESTILO DE NÓMINA ===== --}}
            <div class="card" id="NominaEstudiantes" style="display: none;">
                <div class="card-header bg-primary text-white p-2"> {{-- Color cambiado y padding ajustado --}}
                    <div class="row align-items-center" style="width: 100%;">
                        <div class="col-12 col-md-5">
                            <h5 class="mb-0">Nómina de Estudiantes</h5>
                        </div>
                        <div class="col-12 col-md-7 text-md-right"> {{-- Ajuste de alineación --}}
                            <span class="mr-2">Reportes:</span>
                            <button type="button" class="btn btn-info btn-sm" id="goReportePorAsignatura" onclick="ReportePorAsignatura()" title="Reporte Por Asignatura">
                                <i class="fas fa-clipboard-list"></i> Por Asignatura
                            </button>
                            <button type="button" class="btn btn-dark btn-sm" id="goReportePorGrado" onclick="ReportePorGrado()" title="Reporte Por Grado">
                                <i class="fas fa-clipboard-user"></i> Por Grado {{-- Icono cambiado --}}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="width:100%;overflow:auto; max-height:600px;">
                        <table class="table table-sm table-bordered table-condensed table-hover" id="TablaNominaEstudiantes">
                          <thead>
                              <tr class="bg-secondary">
                                <th>N.°</th>
                                <th>NIE</th>
                                <th>Nombre del Estudiante</th>
                                <th>Calificación</th>
                                {{-- ===== 1. AÑADIR ESTA COLUMNA PARA EL CHECKBOX MAESTRO ===== --}}
                                <th class="text-center" style="width: 50px;">
                                    <input type="checkbox" id="checkAllBoletas" title="Seleccionar Todos">
                                </th>
                                <th>Boleta</th> {{-- Añadido título de columna --}}
                              </tr>
                          </thead>
                          <tbody id="contenido">
                              <tr>
                                <td colspan="5" class="text-center">Seleccione los filtros para cargar la nómina.</td>
                              </tr>
                          </tbody>
                        </table>
                      </div>
                </div>
                <div class="card-footer text-right bg-light"> {{-- Alineación de botón --}}
                    {{-- ===== 2. AÑADIR ESTOS BOTONES NUEVOS ===== --}}
                    <button type="button" class="btn btn-info" id="btnVerSeleccionados" style="display: none;">
                        <i class="fas fa-eye"></i> Ver Seleccionados
                    </button>
                    <button type="button" class="btn btn-secondary" id="btnDescargarSeleccionados" style="display: none;">
                        <i class="fas fa-file-download"></i> Descargar Seleccionados
                    </button>
                    <button type="button" class="btn btn-warning" id="btnEnviarCorreos" style="display: none;">
                        <i class="fas fa-paper-plane"></i> Enviar Correos
                    </button>
                    {{-- ===== MEJORA 4: SWEETALERT AL GUARDAR ===== --}}
                    <button type="button" class="btn btn-success" id="goCalificacionGuardar" onclick="ConfirmarGuardarRegistros()">
                        <i class="fas fa-save"></i> Guardar Calificaciones
                    </button>
                </div>
              </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
#collapse{
    max-height:300px;
    }

/* Esta regla se aplica a los <select> de Stisla (custom-select)
  y a cualquier otro input (form-control) que tenga tu clase.
*/
.custom-select.form-control-resaltado,
.form-control.form-control-resaltado {
    background-color: rgb(205, 229, 251) !important; 
    border-color: #fd5502 !important;
}
</style>
@endsection

@section('scripts')
{{-- Añadimos SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        
        {{-- Se eliminó el script huérfano de select2 --}}
        // Al inicio de las 4 funciones de filtro
            $('#btnVerSeleccionados, #btnDescargarSeleccionados, #btnEnviarCorreos').hide();
        // funcion onchange
        function BuscarPorAnnLectivo(AnnLectivo) {
            // ... (tu código AJAX) ...
            url_ajax = '{{url("getGradoSeccion")}}' 
            csrf_token = '{{csrf_token()}}' 

            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();

            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": codigo_personal, 
                    codigo_annlectivo: codigo_annlectivo
                },
                dataType: 'json',
                success:function(data) {
                     var miselect=$("#codigo_grado_seccion_turno");
                             miselect.empty();
                             miselect.append('<option value="">Seleccionar...</option>');
                                 $.each( data, function( key, value ) {
                                        //console.log(value.codigo_gradoseccionturno);
                                        //console.log(value.nombre_gradoseccionturno);
                                            miselect.append('<option value="' + value.codigo_gradoseccionturno + '">' + value.nombre_gradoseccionturno + '</option>'); 
                                });
                } 
            });
        }
        // function onchange- CUANDO SELECCIONO LA ASIGNATURA.
        function BuscarPorAsignatura(CodigoAsignatura) {
            // SELECCIONAR EL PRIMERO ELEMENTO DE CADA SELECT Y LIMPIAR LA TABLA.
                $('#codigo_periodo').val('00');
                $('#codigo_actividad_porcentaje').val('00');
                $('#codigo_area').val(CodigoAsignatura);
                $('#contenido').empty().append('<tr><td colspan="5" class="text-center">Seleccione Período y Actividad...</td></tr>');
        }
       // funcion onchange. CUANDO SELECCIONO EL GRADO Y SECCION
        function BuscarPorGradoSeccionAsignaturas(GradoSeccion) {
            url_ajax = '{{url("getGradoSeccionAsignaturas")}}' 
            csrf_token = '{{csrf_token()}}' 
            // VARIABLES
            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();
            // BUSCAR LA CARGA ACADEMICA DEL DOCENTE.
            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": codigo_personal, 
                    codigo_annlectivo: codigo_annlectivo,
                    codigo_gradoseccionturno: GradoSeccion
                },
                dataType: 'json',
                success:function(data) {
                     var miselect=$("#codigo_asignatura");
                             miselect.empty();
                             miselect.append('<option value="00">Seleccionar...</option>');
                                 $.each( data, function( key, value ) {
                                        console.log("codigo_asignatura: " + value.codigo_asignatura + " codigo area: " + value.codigo_area + " Nombre: " + value.nombre_asignatura);
                                            miselect.append('<option value="' + value.codigo_asignatura + value.codigo_area + '">' + value.nombre_asignatura + '</option>'); 
                                    // rellenar hidden
                                        $("#codigo_area").val(value.codigo_area);
                                    // SELECCIONAR EL PRIMERO ELEMENTO DE CADA SELECT Y LIMPIAR LA TABLA.
                                        $('#codigo_asignatura option:nth-child(1)').val();
                                        $('#codigo_periodo').val('00');
                                        $('#codigo_actividad_porcentaje').val('00');
                                        $('#contenido').empty().append('<tr><td colspan="5" class="text-center">Seleccione Asignatura...</td></tr>');
                                });
                } 
            });
            // BUSCAR DEPENDIENDO DE LA FECHA EL PERIODO PARA HABILITAR.
            //
            url_ajax = '{{url("getPeriodo")}}' 
            csrf_token = '{{csrf_token()}}' 

            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();
            // BUSCAR LA CARGA ACADEMICA DEL DOCENTE.
            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": codigo_personal, 
                    codigo_annlectivo: codigo_annlectivo,
                    codigo_gradoseccionturno: GradoSeccion
                },
                dataType: 'json',
                success:function(data) {
                     var miselect=$("#codigo_periodo");
                             miselect.empty();
                             miselect.append('<option value="">Seleccionar...</option>');
                                 $.each( data, function( key, value ) {
                                        console.log("codigo_periodo: " + value.codigo_periodo + " Nombre: " + value.nombre_periodo + " Fecha: " + value.fecha + " Fecha desde: " + value.fecha_desde);
                                            miselect.append('<option value="' + value.codigo_periodo + '">' + value.nombre_periodo + '</option>'); 
                                    // rellenar hidden
                                        //$("#codigo_area").val(value.codigo_area);
                                    // SELECCIONAR EL PRIMERO ELEMENTO DE CADA SELECT Y LIMPIAR LA TABLA.
                                        $('#codigo_periodo option:nth-child(1)').val();
                                        $('#codigo_periodo').val('00');
                                        $('#codigo_actividad_porcentaje').val('00');
                                        $('#contenido').empty().append('<tr><td colspan="5" class="text-center">Seleccione Asignatura...</td></tr>');
                                });
                } 
            });
        }
        // funcion onchange
        function BuscarPorPeriodo(Periodo) {
            // ... (tu código de lógica de períodos) ...
            codigo_asignatura_area = $("#codigo_asignatura").val();
            
            conteo_codigo_asignatura = codigo_asignatura_area.length;
            console.log("Conteo asignatura: " + conteo_codigo_asignatura);
            if(conteo_codigo_asignatura == 4){
                codigo_asignatura = codigo_asignatura_area.substring(0,2);
                codigo_area = codigo_asignatura_area.substring(2,4);
                console.log("Código Asignatura: " + codigo_asignatura);
                console.log("Código Area: " + codigo_area);
            }else if(conteo_codigo_asignatura == 6){
                codigo_asignatura = codigo_asignatura_area.substring(0,4);
                codigo_area = codigo_asignatura_area.substring(4,6);
                console.log("Código Asignatura:  " + codigo_asignatura);
                console.log("Código Area:  " + codigo_area);
            }
            else{
                codigo_asignatura = codigo_asignatura_area.substring(0,3);
                codigo_area = codigo_asignatura_area.substring(3,5);
                console.log("Código Asignatura: " + codigo_asignatura);
                console.log("Código Area: " + codigo_area);
            }
            if(codigo_area == '01' || codigo_area == '02' || codigo_area == '03' || codigo_area == '08'){
                miselect = $("#codigo_actividad_porcentaje");
                miselect.empty();
                // Evaluar si es recuperación o Actividades.
                // Extraer datos del periodo
                    codigo_periodo = $("#codigo_periodo").val();
                    var valor_periodo = $("#codigo_periodo option:selected");
                    // enviar resultados a la consola
                        console.log(valor_periodo.val() + " Texto: "  + valor_periodo.text());
                    //
                    if(valor_periodo.val() == '06' || valor_periodo.val() == '07' || codigo_asignatura == '234' || valor_periodo.val() == '08'){
                        miselect.append('<option value=00 selected>Seleccionar...</option>'); 
                        miselect.append('<option value='+valor_periodo.val()+'>'+valor_periodo.text()+'</option>'); 
                    }else{
                        miselect.append('<option value=00 selected>Seleccionar...</option>'); 
                        miselect.append('<option value=01>Actividad 1 (35%)</option>'); 
                        miselect.append('<option value=02>Actividad 2 (35%)</option>'); 
                        miselect.append('<option value=03>Examen o Prueba Objetiva (30%)</option>'); 
                        miselect.append('<option value=04>Recuperación</option>'); 
                    }
            }else{
                // Extraer datos del periodo
                codigo_periodo = $("#codigo_periodo").val();
                var valor_periodo = $("#codigo_periodo option:selected");
                // enviar resultados a la consola
                    console.log(valor_periodo.val() + " Texto: "  + valor_periodo.text());
                //
                miselect = $("#codigo_actividad_porcentaje");
                miselect.empty();
                miselect.append('<option value=00 selected>Seleccionar...</option>'); 
                miselect.append('<option value='+valor_periodo.val()+'>'+valor_periodo.text()+'</option>'); 
                    // Llamar a la funciond e busqueda
                    BuscarPorActividadPorcentaje(Periodo);
            }
            // Botón Otro... visible.
            $("#NominaEstudiantes").css("display","none");
            // 	lIMPIAR SECTION QUE CONTIENE EL PORTAFOLIO.
		    $('#ListarPortafolio').empty();
            // SELECCIONAR EL PRIMERO ELEMENTO DE CADA SELECT Y LIMPIAR LA TABLA.
                $('#codigo_actividad_porcentaje').val('00');
                $('#contenido').empty().append('<tr><td colspan="5" class="text-center">Seleccione Actividad...</td></tr>');
        }
        // funcion onchange. CUANDO SELECCIONO EL PERIODO
        function BuscarPorActividadPorcentaje(ActividadPorcentaje) {
			// Botón Otro... visible.
				$("#NominaEstudiantes").css("display","block");
            // Evaluar si es 00
                if(ActividadPorcentaje == '00'){
                    $("#contenido").empty().append('<tr><td colspan="5" class="text-center">Seleccione una Actividad...</td></tr>');
                    return;
                }
            url_ajax = '{{url("getGradoSeccionCalificacionesAsignaturas")}}'; 
            csrf_token = '{{csrf_token()}}'; 

            codigo_personal = $('#codigo_personal').val();
            var codigo_annlectivo = $('#codigo_annlectivo').val();
            codigo_asignatura_area = $("#codigo_asignatura").val();

            conteo_codigo_asignatura = codigo_asignatura_area.length;
            if(conteo_codigo_asignatura == 4){
                codigo_asignatura = codigo_asignatura_area.substring(0,2);
                codigo_area = codigo_asignatura_area.substring(2,4);
            }else if(conteo_codigo_asignatura == 6){
                codigo_asignatura = codigo_asignatura_area.substring(0,4);
                codigo_area = codigo_asignatura_area.substring(4,6);
                console.log("Código Asignatura:  " + codigo_asignatura);
                console.log("Código Area:  " + codigo_area);
            }
            else{
                codigo_asignatura = codigo_asignatura_area.substring(0,3);
                codigo_area = codigo_asignatura_area.substring(3,5);
            }
            // CDIGO PRERIO, GRADOSECCIONTURNO - CODIGO INSTTIUCION
                codigo_periodo = $("#codigo_periodo").val();
                //console.log("Código Período: " + codigo_periodo);
                // codigo periodo 2 digitos. 
                codigo_gradoseccionturno = $("#codigo_grado_seccion_turno").val();
                // codigo modalidad.
                codigo_modalidad = codigo_gradoseccionturno.substring(6,8);
                    console.log("Codigo Modalidad: " + codigo_modalidad);
                codigo_institucion = $("#codigo_institucion").val();
            // VALIDAR SELECT ANTES DE CONSULTAR LA INFORMACIÓN
            //
            //
                if(codigo_asignatura_area == "" || codigo_asignatura_area == "00"){
                    // Display an info toast with no title
                        toastr.error("!Seleccione la Asignatura!", "Sistema");
                        $("#NominaEstudiantes").css("display","none");
                        exit;
                }
            // MUESTRA CARGANDO
            $('#contenido').empty().append('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando nómina...</td></tr>');

            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    codigo_annlectivo: codigo_annlectivo,
                    codigo_gradoseccionturno: codigo_gradoseccionturno,
                    codigo_asignatura: codigo_asignatura,
                    codigo_area: codigo_area,
                    codigo_actividad: ActividadPorcentaje,
                    codigo_periodo: codigo_periodo
                },
                dataType: 'json',
                success:function(data) {
                    var linea = 0; var html= "";
                    $('#contenido').empty();
                    //$('#contenido').append(data); // Esto estaba mal, 'data' es JSON
                    $.each( data, function( key, value ) {
                        linea = linea + 1;
                        // validar para cambiar de color la l{inea}
                        if (linea % 2 === 0) {
                            fila_color = '<tr style=background:#E2EAF4; text-color:black;>';
                        }else{
                            fila_color = '<tr style=background: #FFFFFF; text-color:black;>';
                        }
                        // validar si es cero la calificación
                        if(parseFloat(value.nota_actividad) == 0){
                            style = " style='background: #FFC5C5; color: #FA4646;'";
                        }else{
                            style = " style='background: #FAFAFA; color: black;'";
                        } 
                        // ARMAR VARIABLE QUE CONTENGA LOS DATOS PARA PODER OBTENER LA INFORMACION DE LA BOLETA DE CALIFICACIONES
                        //
                            var codigo_nie = value.codigo_nie;
                            var codigo_alumno = value.codigo_alumno;
                            var codigo_matricula = value.codigo_matricula;

                            // String completo para generar PDF (igual al de la boleta individual)
                            var datos_estudiantes_pdf = codigo_nie.trim() + "-" + codigo_alumno + "-" + value.codigo_matricula + "-" + codigo_gradoseccionturno + "-" + codigo_annlectivo.trim() +"-"+ codigo_institucion.trim() + "-"+ codigo_personal;
                            // Email del estudiante (como lo pediste)
                            var email_estudiante = codigo_nie.trim() + '@clases.edu.sv';

                            var datos_estudiantes = codigo_nie.trim() + "-" + codigo_alumno + "-" + value.codigo_matricula + "-" + codigo_gradoseccionturno + "-" + codigo_annlectivo.trim() +"-"+ codigo_institucion.trim() + "-"+ codigo_personal;
                            var descargar_si = "-SI";
                            var descargar_no = "-NO";
                        // ARMAR URL
                            var url = '{{ url("/pdf", "id") }}';
                            url = url.replace('id', datos_estudiantes);
                        //
                        //  armar el thml de la tabla.
                        //  EN ESTE APARTADO QUE DIFERENCIA CUANDO ES PERIODO NORMAL Y PERIODO EXTAORDINARIO.
                        //  
                            var valor_nota_final = 0; var valor_bm = "";
                            if(codigo_periodo == '06' || codigo_periodo == '07'){

                                if(codigo_modalidad >= '03' && codigo_modalidad <= '05'){ // EDUCACI{ON BASICA}
                                    valor_nota_final = 5; valor_bm = "Basica";
                                }else if(codigo_modalidad >= '06' && codigo_modalidad <= '09'){   // EDUCACION MEDIA
                                    valor_nota_final = 6; valor_bm = "Media";
                                    console.log("valor: " + valor_nota_final + " valor m: " + valor_bm);
                                }else if(codigo_modalidad == '10' || codigo_modalidad == '12'){   // NOCTURNA BASICA
                                    valor_nota_final = 5; valor_bm = "Basica";
                                }else if(codigo_modalidad == '11'){   // NOCTURNA MEDIA
                                    valor_nota_final = 6; valor_bm = "Media";
                                }else{
                                    valor_nota_final = 5;
                                }

                                // validar matricula final. EDUCACIÓN BÁSICA
                                    if(value.nota_final < valor_nota_final && valor_bm == "Basica"){
                                        html += fila_color +
                                            '<td>' + linea + '</td>' +
                                            '<td>' + value.codigo_nie + '</td>' +
                                            '<td>' + value.full_name + '</td>' +
                                            "<td><input type=number step=0.1 class=form-control name=calificacion id=calificacion value=" + value.nota_actividad + " max=10.0 min=0.0 maxlength=4 " + style + " oninput='maxLengthNumber(this)'>" +
                                                "<input type=hidden class=form-control name=codigo_calificacion id=codigo_calificacion value=" + value.id_notas + ">"+
                                                "<input type=hidden name=_method value=PUT>"+"</td>" +
                                            // ===== 3. AÑADIR EL TD PARA EL CHECKBOX INDIVIDUAL =====
                                            '<td class="text-center">' +
                                                            '<input type="checkbox" class="check-boleta" ' +
                                                            'data-url-ver="'+url+descargar_no+'" ' +
                                                            'data-url-descargar="'+url+descargar_si+'" '+
                                                            'data-email="'+email_estudiante+'" ' +           // <-- AÑADIDO
                                                            'data-pdf-string="'+datos_estudiantes_pdf+'">' + // <-- AÑADIDO
                                                        '</td>' +
                                                '<td><a class="btn btn-info btn-sm" target="_blank" href="'+url+descargar_no+'"><i class="fas fa-file"></i>'+
                                                '<a class="btn btn-secondary btn-sm" target="_blank" href="'+url+descargar_si+'"><i class="fas fa-download"></i></td>'+
                                            '</tr>';
                                    }
                                // validar matricula final. EDUCACIÓN MEDIA
                                if(value.nota_final < valor_nota_final && valor_bm == "Media"){
                                        html += fila_color +
                                            '<td>' + linea + '</td>' +
                                            '<td>' + value.codigo_nie + '</td>' +
                                            '<td>' + value.full_name + '</td>' +
                                            "<td><input type=number step=0.1 class=form-control name=calificacion id=calificacion value=" + value.nota_actividad + " max=10.0 min=0.0 maxlength=4 " + style + " oninput='maxLengthNumber(this)'>" +
                                                "<input type=hidden class=form-control name=codigo_calificacion id=codigo_calificacion value=" + value.id_notas + ">"+
                                                "<input type=hidden name=_method value=PUT>"+"</td>" +
                                            // ===== 3. AÑADIR EL TD PARA EL CHECKBOX INDIVIDUAL =====
                                            '<td class="text-center">' +
                                                            '<input type="checkbox" class="check-boleta" ' +
                                                            'data-url-ver="'+url+descargar_no+'" ' +
                                                            'data-url-descargar="'+url+descargar_si+'" '+
                                                            'data-email="'+email_estudiante+'" ' +           // <-- AÑADIDO
                                                            'data-pdf-string="'+datos_estudiantes_pdf+'">' + // <-- AÑADIDO
                                                        '</td>' +
                                                '<td><a class="btn btn-info btn-sm" target="_blank" href="'+url+descargar_no+'"><i class="fas fa-file"></i>'+
                                                '<a class="btn btn-secondary btn-sm" target="_blank" href="'+url+descargar_si+'"><i class="fas fa-download"></i></td>'+
                                            '</tr>';
                                    }
                            }else{
                                html += fila_color +
                                '<td>' + linea + '</td>' +
                                '<td>' + value.codigo_nie + '</td>' +
                                '<td>' + value.full_name + '</td>' +
                                "<td><input type=number step=0.1 class=form-control name=calificacion id=calificacion value=" + value.nota_actividad + " max=10.0 min=0.0 maxlength=4 " + style + " oninput='maxLengthNumber(this)'>" +
                                    "<input type=hidden class=form-control name=codigo_calificacion id=codigo_calificacion value=" + value.id_notas + ">"+
                                    "<input type=hidden name=_method value=PUT>"+"</td>" +
                                // ===== 3. AÑADIR EL TD PARA EL CHECKBOX INDIVIDUAL =====
                                '<td class="text-center">' +
                                                            '<input type="checkbox" class="check-boleta" ' +
                                                            'data-url-ver="'+url+descargar_no+'" ' +
                                                            'data-url-descargar="'+url+descargar_si+'" '+
                                                            'data-email="'+email_estudiante+'" ' +           // <-- AÑADIDO
                                                            'data-pdf-string="'+datos_estudiantes_pdf+'">' + // <-- AÑADIDO
                                                        '</td>' +
                                    '<td><a class="btn btn-info btn-sm" target="_blank" href="'+url+descargar_no+'"><i class="fas fa-file-alt"></i></a> '+
                                    '<a class="btn btn-secondary btn-sm" target="_blank" href="'+url+descargar_si+'"><i class="fas fa-download"></i></a></td>'+
                                '</tr>';
                            }
                            
                    });
                    $('#contenido').html(html);
                    $('#contenido').focus();
                        // ===== 4. MOSTRAR BOTONES Y MENSAJE =====
                        if(linea > 0){
                            toastr.success("Registros Encontrados... " + linea, "Sistema");
                            // Mostrar todos los botones de acciones masivas
                            $('#btnVerSeleccionados, #btnDescargarSeleccionados, #btnEnviarCorreos').show();
                        } else {
                            // ... (tu toastr.info) ...
                            $('#btnVerSeleccionados, #btnDescargarSeleccionados, #btnEnviarCorreos').hide();
                        }
                } 
            });
        }
        // Reporte de Calificaciones por asignatura.
        function ReportePorAsignatura() {
            // ... (tu código) ...
             var codigo_gradoseccionturno = $("#codigo_grado_seccion_turno").val();
            var codigo_annlectivo = $('#codigo_annlectivo').val();
            var codigo_asignatura_area = $("#codigo_asignatura").val();
            var codigo_personal = $('#codigo_personal').val();
            
            var conteo_codigo_asignatura = codigo_asignatura_area.length;
            if(conteo_codigo_asignatura == 4){
                codigo_asignatura = codigo_asignatura_area.substring(0,2);
                codigo_area = codigo_asignatura_area.substring(2,4);
            }else if(conteo_codigo_asignatura == 6){
                codigo_asignatura = codigo_asignatura_area.substring(0,4);
                codigo_area = codigo_asignatura_area.substring(4,6);
                console.log("Código Asignatura:  " + codigo_asignatura);
                console.log("Código Area:  " + codigo_area);
            }else{
                codigo_asignatura = codigo_asignatura_area.substring(0,3);
                codigo_area = codigo_asignatura_area.substring(3,5);
            }

            var datos_estudiantes = codigo_gradoseccionturno + "-" + codigo_annlectivo.trim() +"-"+ codigo_institucion.trim()+"-"+codigo_asignatura+"-"+codigo_area+"-"+codigo_personal;
            // ARMAR URL
                var url = '{{ url("/pdfRPA", "id") }}';
                url = url.replace('id', datos_estudiantes);
            // abrir ventana emergente con el pdf de las califiaciones por asignatura.
                AbrirVentana(url);
        }
        // Reporte de Calificaciones por asignatura.
        function ReportePorGrado() {
            // ... (tu código) ...
            var codigo_gradoseccionturno = $("#codigo_grado_seccion_turno").val();
            var codigo_annlectivo = $('#codigo_annlectivo').val();
            var codigo_asignatura_area = $("#codigo_asignatura").val();
            var codigo_personal = $('#codigo_personal').val();
            
            var conteo_codigo_asignatura = codigo_asignatura_area.length;
            if(conteo_codigo_asignatura == 4){
                codigo_asignatura = codigo_asignatura_area.substring(0,2);
                codigo_area = codigo_asignatura_area.substring(2,4);
            }else if(conteo_codigo_asignatura == 6){
                codigo_asignatura = codigo_asignatura_area.substring(0,4);
                codigo_area = codigo_asignatura_area.substring(4,6);
                console.log("Código Asignatura:  " + codigo_asignatura);
                console.log("Código Area:  " + codigo_area);
            }else{
                codigo_asignatura = codigo_asignatura_area.substring(0,3);
                codigo_area = codigo_asignatura_area.substring(3,5);
            }

            var datos_estudiantes = codigo_gradoseccionturno + "-" + codigo_annlectivo.trim() +"-"+ codigo_institucion.trim()+"-"+codigo_asignatura+"-"+codigo_area+"-"+codigo_personal;
            // ARMAR URL
                var url = '{{ url("/pdfRPG", "id") }}';
                url = url.replace('id', datos_estudiantes);
            // abrir ventana emergente con el pdf de las califiaciones por asignatura.
                AbrirVentana(url);
        }
        
        {{-- ===== MEJORA 5: FUNCIÓN DE CONFIRMACIÓN ===== --}}
        function ConfirmarGuardarRegistros() {
            Swal.fire({
                title: '¿Guardar Calificaciones?',
                text: "Se actualizarán todas las notas en la nómina actual. Esta acción no se puede deshacer.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745', // Verde
                cancelButtonColor: '#6c757d', // Gris
                confirmButtonText: 'Sí, guardar ahora',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si confirma, llama a la función original de guardado
                    GuardarRegistros();
                }
            });
        }

        // funcionar para guardar las calificaciones. (Esta es la función original)
        function GuardarRegistros() {
            csrf_token = '{{csrf_token()}}';

            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();
            codigo_asignatura_area = $("#codigo_asignatura").val();
            conteo_codigo_asignatura = codigo_asignatura_area.length;
            if(conteo_codigo_asignatura == 4){
                codigo_asignatura = codigo_asignatura_area.substring(0,2);
                codigo_area = codigo_asignatura_area.substring(2,4);
            }else if(conteo_codigo_asignatura == 6){
                codigo_asignatura = codigo_asignatura_area.substring(0,4);
                codigo_area = codigo_asignatura_area.substring(4,6);
                console.log("Código Asignatura:  " + codigo_asignatura);
                console.log("Código Area:  " + codigo_area);
            }else{
                codigo_asignatura = codigo_asignatura_area.substring(0,3);
                codigo_area = codigo_asignatura_area.substring(3,5);
            }
            codigo_actividad = $("#codigo_actividad_porcentaje").val();
            codigo_periodo = $("#codigo_periodo").val();
            codigo_gradoseccionturno = $("#codigo_grado_seccion_turno").val();
            // leer tabla de datos con ID y calificaciòn.
            var $objCuerpoTabla=$("#TablaNominaEstudiantes").children().prev().parent();
                var codigo_calificacion_ = []; var calificacion_ = [];               
                var fila = 0;
                // recorre el contenido de la tabla.
                $objCuerpoTabla.find("tbody tr").each(function(){
                                var codigo_calificacion =$(this).find('td').eq(3).find("input[name='codigo_calificacion']").val();
                                var calificacion =$(this).find('td').eq(3).find("input[name='calificacion']").val();
                        // dar valor a las arrays.
                        codigo_calificacion_[fila] = codigo_calificacion;
                        calificacion_[fila] = calificacion;
                        fila = fila + 1;
                });
                url_ajax = "{{ URL('/getActualizarCalificacion') }}"; 
            //////
            $.ajax({
                type: "PUT",
                url: url_ajax,
                data: {
                    _token:'{{ csrf_token() }}',
                    codigo_annlectivo: codigo_annlectivo,
                    codigo_gradoseccionturno: codigo_gradoseccionturno,
                    codigo_asignatura: codigo_asignatura,
                    codigo_area: codigo_area,
                    codigo_actividad: codigo_actividad,
                    codigo_periodo: codigo_periodo,
                    codigo_calificacion: codigo_calificacion_,
                    calificacion: calificacion_,
                    fila: fila
                },
                dataType: 'json',
                success:function(data) {
                   $('#codigo_annlectivo').focus();
                    // Display an info toast with no title
                    toastr.success("¡Calificaciones actualizadas exitosamente!", "Sistema");
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Añadido manejo de error
                    toastr.error("Error al guardar: " + errorThrown, "Sistema");
                }
            });
        }
        // MAXIM DE NUMEROS DEPENDE DE LA CALIFIACIÓN.
        function maxLengthNumber(valor) {
            // ... (tu código) ...
            // Extraer el codigo area y codigo modalidad.
                codigo_modalidad = codigo_gradoseccionturno.substring(6,8);
                codigo_asignatura_area = $("#codigo_asignatura").val();
                conteo_codigo_asignatura = codigo_asignatura_area.length;
                var MaximaValorCalificacion = 10;
            // comprar para extraer codigo area.
                if(conteo_codigo_asignatura == 4){
                    codigo_asignatura = codigo_asignatura_area.substring(0,2);
                    codigo_area = codigo_asignatura_area.substring(2,4);
                }else if(conteo_codigo_asignatura == 6){
                codigo_asignatura = codigo_asignatura_area.substring(0,4);
                codigo_area = codigo_asignatura_area.substring(4,6);
                console.log("Código Asignatura:  " + codigo_asignatura);
                console.log("Código Area:  " + codigo_area);
                }else{
                    codigo_asignatura = codigo_asignatura_area.substring(0,3);
                    codigo_area = codigo_asignatura_area.substring(3,5);
                }
            //
            if(codigo_area == '03' && codigo_modalidad == "15"){
                MaximaValorCalificacion = 5;
            }
            //
            console.log(valor.value);
            {
		    var amount = valor.value;
		    console.log(amount);
            //d+ permite caracteres enteros
            //si hay un caracter que no es dígito entonces evalua lo que está en paréntesis (?) significa opcional
		    var patron = /^(\d+(.{1}\d{1})?)$/;     		    
                    if (!patron.test(amount))
		   		 	{
		       	 		console.log('cantidad ingresada incorrectamente');
		        		valor.value = "";
		        		return false;
        			}
		   			 else if(amount > MaximaValorCalificacion){
                     console.log('cantidad ingresada incorrectamente');
                     valor.value = "";
                     return false;
                     }
                     else{
		        		return true;}
		  }
        }
        // ABRIR NUEVA PESTAÑA PARA LOS DIFERENTES INFORMES.
        function AbrirVentana(url)
            {
                window.open(url, '_blank');
                return false;
            }
    

        // ===== 5. LÓGICA PARA CHECKBOXES Y ACCIONES MASIVAS =====

        // Función para "Seleccionar Todos"
        $('#TablaNominaEstudiantes').on('click', '#checkAllBoletas', function() {
            // 'this.checked' es true si el master checkbox está marcado
            // Busca todos los checkboxes con clase '.check-boleta' dentro del tbody
            $('#TablaNominaEstudiantes tbody .check-boleta').prop('checked', this.checked);
        });

        // Función para desmarcar "Seleccionar Todos" si un checkbox individual se desmarca
        $('#TablaNominaEstudiantes').on('click', '.check-boleta', function() {
            if (!this.checked) {
                $('#checkAllBoletas').prop('checked', false);
            }
        });

        // Listeners para los nuevos botones
        $(document).ready(function() {
            $('#btnVerSeleccionados').on('click', function() {
                AccionMasivaBoletas('ver');
            });

            $('#btnDescargarSeleccionados').on('click', function() {
                AccionMasivaBoletas('descargar');
            });
            // ===== AÑADIR ESTE LISTENER =====
            $('#btnEnviarCorreos').on('click', function() {
                ConfirmarEnvioCorreos();
            });
        });

        // === ¡LA NUEVA FUNCIÓN QUE PEDISTE! ===
        function AccionMasivaBoletas(accion) {
            var urls = [];
            var tipoAccion = (accion === 'ver') ? 'Ver' : 'Descargar';
            var dataAttr = (accion === 'ver') ? 'data-url-ver' : 'data-url-descargar';
            var confirmColor = (accion === 'ver') ? '#3085d6' : '#5c6c7a'; // Azul para ver, gris para descargar

            // 1. Recolectar todas las URLs de los checkboxes marcados
            $('.check-boleta:checked').each(function() {
                urls.push($(this).attr(dataAttr));
            });

            // 2. Validar si seleccionó alguno
            if (urls.length === 0) {
                Swal.fire('Ningún Estudiante', 'Por favor, seleccione al menos un estudiante de la lista.', 'info');
                return;
            }

            // 3. ¡Mostrar el SweetAlert!
            Swal.fire({
                title: '¿' + tipoAccion + ' ' + urls.length + ' Boleta(s)?',
                text: "Esto intentará abrir " + urls.length + " pestaña(s) nueva(s). Su navegador podría bloquearlas. ¿Desea continuar?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 4. Si confirma, abrir todas las pestañas
                    toastr.info('Iniciando ' + tipoAccion + '... Por favor, revise si su navegador bloqueó las pestañas.', 'Sistema');
                    urls.forEach(function(url) {
                        window.open(url, '_blank');
                    });
                }
            });
        }
    
    /**
         * Muestra la confirmación de SweetAlert antes de enviar correos.
         */
         function ConfirmarEnvioCorreos() {
            var estudiantes = [];
            
            // 1. Recolectar datos de los checkboxes marcados
            $('.check-boleta:checked').each(function() {
                estudiantes.push({
                    email: $(this).data('email'),
                    datos_pdf: $(this).data('pdf-string')
                });
            });

            // 2. Validar si seleccionó alguno
            if (estudiantes.length === 0) {
                Swal.fire('Ningún Estudiante', 'Por favor, seleccione al menos un estudiante para enviar el correo.', 'info');
                return;
            }

            // 3. ¡Mostrar el SweetAlert!
            Swal.fire({
                title: '¿Enviar ' + estudiantes.length + ' Correo(s)?',
                html: "Se enviarán las boletas a las direcciones institucionales (<b>NIE@clases.edu.sv</b>).<br><br>Esta acción se pondrá en cola y puede tardar unos minutos en completarse.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107', // Color Naranja (Warning)
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, enviar ahora'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 4. Si confirma, llamar a la función AJAX
                    EnviarCorreosMasivos(estudiantes);
                }
            });
        }

        /**
         * Envía la lista de estudiantes al controlador de Laravel.
         */
        function EnviarCorreosMasivos(estudiantes) {
            // Muestra un 'cargando'
            toastr.info('Encolando correos para envío... Por favor espere.', 'Sistema');

            // Deshabilita el botón para evitar doble clic
            $('#btnEnviarCorreos').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

            $.ajax({
                type: "POST", // Usar POST para enviar un array de datos
                url: "{{ url('calificaciones/enviar-correos') }}", // Crearemos esta ruta
                data: {
                    _token: '{{ csrf_token() }}',
                    estudiantes: estudiantes, // El array de objetos (email y datos_pdf)
                    codigo_institucion: $("#codigo_institucion").val() // Para los datos generales
                },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        Swal.fire('¡Correos Encolados!', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire('Error de Servidor', 'No se pudo contactar al servidor. (' + errorThrown + ')', 'error');
                },
                complete: function() {
                    // Vuelve a habilitar el botón
                    $('#btnEnviarCorreos').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar Correos');
                }
            });
        }
    </script>
@endsection