<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estudiante;
use App\Models\Tablas\Annlectivo;
use App\Models\Tablas\CargaDocente;

class CalificacionesPorAsignaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $annlectivo=Annlectivo::where('estatus', true)->orderBy('codigo', 'desc')->pluck('nombre','codigo')->toarray();
        //$query = DB::table('tablethis')->where('id', $result)->where('type', 'like')->orderBy('created_at', 'desc');
        //$annlectivo = Annlectivo::pluck('nombre','codigo')->toarray();
        return view('CalificacionPorAsignatura.index', compact('annlectivo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function buscarGradoSeccion()
    {
     /*   select DISTINCT cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_seccion, cd.codigo_turno, cd.codigo_ann_lectivo,
            bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno
            from carga_docente cd
            inner join bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato
            inner join grado_ano gr On gr.codigo = cd.codigo_grado
            inner join seccion sec on sec.codigo = cd.codigo_seccion
            inner join turno tur on tur.codigo = cd.codigo_turno
            where cd.codigo_ann_lectivo = '22' and cd.codigo_docente = '19'*/

            $CargaDocente = CargaDocente::join('bachillerato_ciclo', 'bachillerato_ciclo.codigo', '=', 'carga_docente.codigo_bachillerato')
            ->join('grado_ano', 'grado_ano.codigo', '=', 'carga_docente.codigo')
            ->join('seccion', 'seccion.turno', '=', 'carga_docente.codigo')
            ->join('turno','turno.codigo', '=', 'carga_docente.codigo')
            ->select('carga_docente.codigo_bachillerato','carga_docente.codigo_grado','carga_docente.codigo_seccion','carga_docente.codigo_turno','carga_docente.codigo_ann_lectivo',
            'bachillerato.nombre as nombre_modalidad','grado_ano.nombre as nombre_grado','seccion.nombre as nombre_seccion','turno.nombre as nombre_turno')
            ->get();
    }
}
