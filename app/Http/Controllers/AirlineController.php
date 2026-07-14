<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use Illuminate\Http\Request;

class AirlineController extends Controller
{
    public function index()
    {
        $airlines = Airline::all();

        return view('airline.index', [
            'airlines' => $airlines
        ]);
    }

    public function getairlineid($id)
    {
        $airline = Airline::where('id_aerolinea', '=', base64_decode($id))->first();
        return response()->json([$airline]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'iata' => 'required|string|max:5',
                'nombre' => 'required|string|max:60',
            ], [
                'iata.required' => 'El código IATA es requerido',
                'iata.max' => 'El código IATA no puede tener más de 5 caracteres',
                'nombre.required' => 'El nombre es requerido',
                'nombre.max' => 'El nombre no puede tener más de 60 caracteres',
            ]);

            $airline = new Airline();
            $airline->iata = strtoupper($request->iata);
            $airline->nombre = $request->nombre;
            $airline->save();

            return redirect()->route('airline.index')->with('success', 'Aerolínea creada correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al crear aerolínea: ' . $e->getMessage());
            return redirect()->route('airline.index')->with('error', 'Error al crear la aerolínea: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'idedit' => 'required|exists:aerolineas,id_aerolinea',
                'iataedit' => 'required|string|max:5',
                'nombreedit' => 'required|string|max:60',
            ], [
                'idedit.required' => 'ID de aerolínea requerido',
                'idedit.exists' => 'La aerolínea seleccionada no existe',
                'iataedit.required' => 'El código IATA es requerido',
                'iataedit.max' => 'El código IATA no puede tener más de 5 caracteres',
                'nombreedit.required' => 'El nombre es requerido',
                'nombreedit.max' => 'El nombre no puede tener más de 60 caracteres',
            ]);

            $airline = Airline::find($request->idedit);
            if (!$airline) {
                return redirect()->route('airline.index')->with('error', 'Aerolínea no encontrada');
            }

            $airline->iata = strtoupper($request->iataedit);
            $airline->nombre = $request->nombreedit;
            $airline->save();

            return redirect()->route('airline.index')->with('success', 'Aerolínea actualizada correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar aerolínea: ' . $e->getMessage());
            return redirect()->route('airline.index')->with('error', 'Error al actualizar la aerolínea: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $airline = Airline::find(base64_decode($id));
            if ($airline) {
                $airline->delete();
                return response()->json(["res" => "1"]);
            }
            return response()->json(["res" => "0"]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar aerolínea: ' . $e->getMessage());
            return response()->json(["res" => "0", "error" => $e->getMessage()]);
        }
    }
}
