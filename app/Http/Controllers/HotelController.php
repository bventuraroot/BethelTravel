<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HotelController extends Controller
{
    public function index()
    {
        $hotels = Hotel::orderBy('id_hotel', 'desc')->get();
        $destinations = DB::table('aeropuertos')
            ->where('iata', '!=', 'NA')
            ->select('ciudad', 'pais')
            ->distinct()
            ->orderBy('ciudad', 'asc')
            ->get();

        return view('hotel.index', [
            'hotels' => $hotels,
            'destinations' => $destinations
        ]);
    }

    public function gethotelid($id)
    {
        $hotel = Hotel::where('id_hotel', '=', base64_decode($id))->first();
        return response()->json([$hotel]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'destino' => 'required|string|max:100',
            ], [
                'nombre.required' => 'El nombre del hotel es requerido',
                'nombre.max' => 'El nombre del hotel no puede tener más de 100 caracteres',
                'destino.required' => 'El destino es requerido',
                'destino.max' => 'El destino no puede tener más de 100 caracteres',
            ]);

            $hotel = new Hotel();
            $hotel->nombre = $request->nombre;
            $hotel->destino = strtoupper($request->destino);
            $hotel->save();

            return redirect()->route('hotel.index')->with('success', 'Hotel creado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al crear hotel: ' . $e->getMessage());
            return redirect()->route('hotel.index')->with('error', 'Error al crear el hotel: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'idedit' => 'required|exists:hoteles,id_hotel',
                'nombreedit' => 'required|string|max:100',
                'destinoedit' => 'required|string|max:100',
            ], [
                'idedit.required' => 'ID de hotel requerido',
                'idedit.exists' => 'El hotel seleccionado no existe',
                'nombreedit.required' => 'El nombre del hotel es requerido',
                'nombreedit.max' => 'El nombre del hotel no puede tener más de 100 caracteres',
                'destinoedit.required' => 'El destino es requerido',
                'destinoedit.max' => 'El destino no puede tener más de 100 caracteres',
            ]);

            $hotel = Hotel::find($request->idedit);
            if (!$hotel) {
                return redirect()->route('hotel.index')->with('error', 'Hotel no encontrado');
            }

            $hotel->nombre = $request->nombreedit;
            $hotel->destino = strtoupper($request->destinoedit);
            $hotel->save();

            return redirect()->route('hotel.index')->with('success', 'Hotel actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar hotel: ' . $e->getMessage());
            return redirect()->route('hotel.index')->with('error', 'Error al actualizar el hotel: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $hotel = Hotel::find(base64_decode($id));
            if ($hotel) {
                $hotel->delete();
                return response()->json(["res" => "1"]);
            }
            return response()->json(["res" => "0"]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar hotel: ' . $e->getMessage());
            return response()->json(["res" => "0", "error" => $e->getMessage()]);
        }
    }
}
