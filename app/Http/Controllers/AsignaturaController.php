<?php

namespace App\Http\Controllers;

use App\Models\mantenimiento\asignatura\Asignatura;
use Illuminate\Http\Request;

class AsignaturaController extends Controller
{

        // contruir para los roles
        function __construct(){
            $this->middleware('permission:ver-asignatura | crear-asignatura | editar-asignatura | borrar-asignatura', ['only'=>['index']]);
            $this->middleware('permission:crear-asignatura', ['only'=>['create','store']]);
            $this->middleware('permission:editar-asignatura', ['only'=>['edit','update']]);
            $this->middleware('permission:borrar-asignatura', ['only'=>['destroy']]);
        }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $asignatura = Asignatura::paginate(5);
        return view('asignaturas.index', compact('asignatura'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('asignaturas.crear');
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
        request()->validate([
            'nombre'=>'required',
            'codigo'=>'required'
        ]);
        Asignatura::create($request->all());
        return redirect()->route('asignaturas.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\mantenimiento\asignatura\Asignatura  $asignatura
     * @return \Illuminate\Http\Response
     */
    public function show(Asignatura $asignatura)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\mantenimiento\asignatura\Asignatura  $asignatura
     * @return \Illuminate\Http\Response
     */
    public function edit(Asignatura $asignatura)
    {
        //
        return view('asignaturas.editar',compact('asignatura'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\mantenimiento\asignatura\Asignatura  $asignatura
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Asignatura $asignatura)
    {
        //
        request()->validate([
            'nombre'=>'required',
            'codigo'=>'required'
        ]);
        Asignatura::update($request->all());
        return redirect()->route('asignaturas.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\mantenimiento\asignatura\Asignatura  $asignatura
     * @return \Illuminate\Http\Response
     */
    public function destroy(Asignatura $asignatura)
    {
        //
        $asignatura->delete();
        return redirect()->route('asignaturas.index');
    }
}
