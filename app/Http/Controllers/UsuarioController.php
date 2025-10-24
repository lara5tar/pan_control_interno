<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    /**
     * Muestra el listado de usuarios
     */
    public function index()
    {
        // Por ahora solo mostramos la vista
        // Más adelante se puede agregar funcionalidad para obtener usuarios de la API
        return view('usuarios.index');
    }

    /**
     * Muestra el formulario de creación
     */
    public function create()
    {
        return view('usuarios.create');
    }

    /**
     * Almacena un nuevo usuario (congregante + credenciales)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'usuario' => 'required|string|max:50',
            'password' => 'required|string|min:4',
            'rol' => 'required|integer',
        ]);

        try {
            // PASO 1: Crear congregante
            $congreganteResponse = Http::post('https://sistemasdevida.com/pan/altaCongregante.php', [
                'nombre' => $request->nombre,
                'apellidos' => $request->apellidos,
                'fecAlta' => now()->format('d/m/Y'),
                'fecLibro1' => '',
                'sexo' => 'O',
                'edoCivil' => 'S',
                'telCasa' => '',
                'horario' => '',
                'telCelular' => '',
                'calle' => '',
                'entreCalle' => '',
                'colonia' => '',
                'codPostal' => '',
                'ciudad' => '',
                'necesidad' => '',
                'via' => '',
                'observaciones' => '',
                'otraIgl' => 0,
                'iglesia' => '',
                'verifica' => '',
                'fecNac' => '',
                'mail' => '',
                'plataforma' => '0',
                'platAsignacion' => '0',
                'afirmador' => 0,
                'casavida' => 0
            ]);

            if (!$congreganteResponse->successful()) {
                Log::error('Error al crear congregante', [
                    'status' => $congreganteResponse->status(),
                    'body' => $congreganteResponse->body()
                ]);
                return back()->with('error', 'Error al crear el congregante en el sistema externo.')
                    ->withInput();
            }

            $congreganteData = $congreganteResponse->json();
            $idCongregante = $congreganteData['id'] ?? null;

            if (!$idCongregante) {
                Log::error('No se obtuvo ID del congregante', ['response' => $congreganteData]);
                return back()->with('error', 'No se pudo obtener el ID del congregante.')
                    ->withInput();
            }

            // PASO 2: Asignar rol
            $rolResponse = Http::post('https://sistemasdevida.com/pan/altaRol.php', [
                'idCon' => $idCongregante,
                'rol' => $request->rol
            ]);

            if (!$rolResponse->successful()) {
                Log::error('Error al asignar rol', [
                    'idCon' => $idCongregante,
                    'status' => $rolResponse->status(),
                    'body' => $rolResponse->body()
                ]);
                // Continuamos aunque falle el rol
            }

            // PASO 3: Crear credenciales (SIN CIFRADO - contraseña plana)
            $usuarioResponse = Http::post('https://sistemasdevida.com/pan/usuarios.php', [
                'idCon' => $idCongregante,
                'us' => $request->usuario,
                'pass' => $request->password  // Contraseña SIN cifrado
            ]);

            if (!$usuarioResponse->successful()) {
                Log::error('Error al crear usuario', [
                    'idCon' => $idCongregante,
                    'status' => $usuarioResponse->status(),
                    'body' => $usuarioResponse->body()
                ]);
                return back()->with('error', 'Congregante creado, pero error al crear credenciales de acceso.')
                    ->withInput();
            }

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario creado exitosamente. ID Congregante: ' . $idCongregante);

        } catch (\Exception $e) {
            Log::error('Excepción al crear usuario', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error al procesar la solicitud: ' . $e->getMessage())
                ->withInput();
        }
    }
}
