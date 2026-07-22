<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AirportController extends Controller
{
    public function index()
    {
        $airports = Airport::orderBy('id_aeropuerto', 'desc')->get();

        return view('airport.index', [
            'airports' => $airports
        ]);
    }

    public function getairportid($id)
    {
        $airport = Airport::where('id_aeropuerto', '=', base64_decode($id))->first();
        return response()->json([$airport]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'iata' => 'required|string|max:5',
                'ciudad' => 'required|string|max:100',
                'pais' => 'required|string|max:100',
                'continente' => 'nullable|string|max:100',
                'subregion' => 'nullable|string|max:100',
            ], [
                'iata.required' => 'El código IATA es requerido',
                'iata.max' => 'El código IATA no puede tener más de 5 caracteres',
                'ciudad.required' => 'La ciudad es requerida',
                'ciudad.max' => 'La ciudad no puede tener más de 100 caracteres',
                'pais.required' => 'El país es requerido',
                'pais.max' => 'El país no puede tener más de 100 caracteres',
            ]);

            $airport = new Airport();
            $airport->iata = strtoupper($request->iata);
            $airport->ciudad = $request->ciudad;
            $airport->pais = $request->pais;
            $airport->continente = $request->continente ?: 'NA';
            $airport->subregion = $request->subregion ?: 'NA';
            $airport->save();

            return redirect()->route('airport.index')->with('success', 'Aeropuerto creado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al crear aeropuerto: ' . $e->getMessage());
            return redirect()->route('airport.index')->with('error', 'Error al crear el aeropuerto: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'idedit' => 'required|exists:aeropuertos,id_aeropuerto',
                'iataedit' => 'required|string|max:5',
                'ciudadedit' => 'required|string|max:100',
                'paisedit' => 'required|string|max:100',
                'continenteedit' => 'nullable|string|max:100',
                'subregionedit' => 'nullable|string|max:100',
            ], [
                'idedit.required' => 'ID de aeropuerto requerido',
                'idedit.exists' => 'El aeropuerto seleccionado no existe',
                'iataedit.required' => 'El código IATA es requerido',
                'iataedit.max' => 'El código IATA no puede tener más de 5 caracteres',
                'ciudadedit.required' => 'La ciudad es requerida',
                'ciudadedit.max' => 'La ciudad no puede tener más de 100 caracteres',
                'paisedit.required' => 'El país es requerido',
                'paisedit.max' => 'El país no puede tener más de 100 caracteres',
            ]);

            $airport = Airport::find($request->idedit);
            if (!$airport) {
                return redirect()->route('airport.index')->with('error', 'Aeropuerto no encontrado');
            }

            $airport->iata = strtoupper($request->iataedit);
            $airport->ciudad = $request->ciudadedit;
            $airport->pais = $request->paisedit;
            $airport->continente = $request->continenteedit ?: 'NA';
            $airport->subregion = $request->subregionedit ?: 'NA';
            $airport->save();

            return redirect()->route('airport.index')->with('success', 'Aeropuerto actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar aeropuerto: ' . $e->getMessage());
            return redirect()->route('airport.index')->with('error', 'Error al actualizar el aeropuerto: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $airport = Airport::find(base64_decode($id));
            if ($airport) {
                $airport->delete();
                return response()->json(["res" => "1"]);
            }
            return response()->json(["res" => "0"]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar aeropuerto: ' . $e->getMessage());
            return response()->json(["res" => "0", "error" => $e->getMessage()]);
        }
    }
}
