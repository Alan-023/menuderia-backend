<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SessionTable;
use App\Models\DiningSession;

class SessionTableController extends Controller
{
    public function index() {
        return SessionTable::all();
    }

    public function store(Request $request) {
        $request->validate([
            'session_id' => 'required|integer|exists:dining_sessions,session_id',
            'table_id' => 'required|integer|exists:restaurant_tables,table_id'
        ]);

        $enlace = new SessionTable();
        $enlace->session_id = $request->session_id;
        $enlace->table_id = $request->table_id;
        $enlace->save();
        
        return $enlace;
    }

    public function show($id) {
        return SessionTable::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $enlace = SessionTable::findOrFail($id);

        $request->validate([
            'session_id' => 'required|integer|exists:dining_sessions,session_id',
            'table_id' => 'required|integer|exists:restaurant_tables,table_id'
        ]);

        $enlace->session_id = $request->session_id;
        $enlace->table_id = $request->table_id;
        $enlace->save();
        
        return $enlace;
    }

    public function destroy($id) {
        $enlace = SessionTable::findOrFail($id);
        $enlace->delete();
        return ["mensaje" => "Mesa desvinculada de la sesion con exito", "id_borrado" => $id];
    }

    // ===== ZONA MESERO =====
    
    // Vincular una mesa a una sesión del mesero
    public function vincularMesaMesero(Request $request, $session_id) {
        $mesero = $request->user();
        
        $sesion = DiningSession::find($session_id);
        if (!$sesion) {
            return response()->json(['mensaje' => 'Sesión no encontrada'], 404);
        }
        if ($sesion->opened_by_user_id != $mesero->user_id) {
            return response()->json(['mensaje' => 'Esta sesión pertenece a otro mesero'], 403);
        }

        $request->validate([
            'table_id' => 'required|integer|exists:restaurant_tables,table_id'
        ]);

        // Verificar si la mesa ya está vinculada
        $existe = SessionTable::where('session_id', $session_id)
            ->where('table_id', $request->table_id)
            ->first();

        if ($existe) {
            return response()->json(['mensaje' => 'Esta mesa ya está vinculada a la sesión'], 409);
        }

        $enlace = new SessionTable();
        $enlace->session_id = $session_id;
        $enlace->table_id = $request->table_id;
        $enlace->save();

        return response()->json([
            'mensaje' => 'Mesa vinculada exitosamente',
            'enlace' => $enlace
        ], 201);
    }
}