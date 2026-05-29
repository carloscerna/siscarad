<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alumno;
use App\Models\AlumnoEncargado;
use Illuminate\Support\Facades\Storage;

class AlumnoInformacionController extends Controller
{
    public function index(Request $request)
{
    $buscar = $request->get('buscar');

    $alumnos = Alumno::with('encargadoPrincipal')
        ->when($buscar, function($query) use ($buscar) {
            $query->where('nombre_completo', 'LIKE', "%{$buscar}%")
                  ->orWhere('codigo_nie', 'LIKE', "%{$buscar}%")
                  ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%")
                  ->orWhere('apellido_materno', 'LIKE', "%{$buscar}%");
        })
        ->orderBy('apellido_paterno', 'asc')
        ->paginate(15); 

    // OJO AQUÍ: Debe decir 'layouts.index_informacion' (la lista), NO 'layouts.informacion'
    return view('layouts.index_informacion', compact('alumnos', 'buscar'));
}

    /**
     * 2. Muestra el formulario con la cámara y firma del alumno seleccionado
     */
    public function edit($id_alumno)
    {
        // Cargamos el alumno con su encargado
        $alumno = Alumno::with('encargadoPrincipal')->findOrFail($id_alumno);

        // CAMBIO AQUÍ: Apunta a tu carpeta layouts
        return view('layouts.informacion', compact('alumno'));
    }

    /**
     * 3. Procesa los datos del formulario
     */
    public function update(Request $request, $id_alumno)
    {
        $alumno = Alumno::findOrFail($id_alumno);
        
        // Procesar foto de la cámara
        if ($request->hasFile('foto')) {
            if ($alumno->foto && Storage::disk('public')->exists($alumno->foto)) {
                Storage::disk('public')->delete($alumno->foto);
            }
            $alumno->foto = $request->file('foto')->store('fotos_alumnos', 'public');
        }

        $alumno->direccion_alumno = $request->direccion_alumno;
        $alumno->save();

        // Guardar encargado
        $encargado = AlumnoEncargado::updateOrCreate(
            [
                'codigo_alumno' => $alumno->id_alumno,
                'encargado' => true
            ],
            [
                'nombres'   => $request->nombres_encargado,
                'telefono'  => $request->telefono_encargado,
                'direccion' => $request->direccion_encargado,
            ]
        );

        // Guardar firma
        if ($request->filled('firma_autorizacion_base64')) {
            $encargado->firma_autorizacion = $request->firma_autorizacion_base64;
            $encargado->save();
        }

        return redirect()->route('estudiante.informacion.index')->with('success', '¡Información, fotografía y firma actualizadas!');
    }
}