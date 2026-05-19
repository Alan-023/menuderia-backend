<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiningSession;

class DiningSessionController extends Controller
{
    public function index() {
        return DiningSession::all();
    }

    public function store(Request $request) {
        $request->validate([
            'session_token' => 'required|string|max:64|unique:dining_sessions,session_token',
            'titular_name' => 'required|string|max:100',
            // El cadenero solo deja pasar estos 3 estatus exacto
            'status' => 'nullable|in:ACTIVE,PAYING,CLOSED',
            'opened_by_user_id' => 'nullable|integer|exists:users,user_id'
        ]);

        $sesion = new DiningSession();
        $sesion->session_token = $request->session_token;
        $sesion->titular_name = $request->titular_name;
        $sesion->status = $request->has('status') ? $request->status : 'ACTIVE';
        $sesion->opened_by_user_id = $request->opened_by_user_id;
        $sesion->save();
        
        return $sesion;
    }

    public function show($id) {
        return DiningSession::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $sesion = DiningSession::findOrFail($id);

        $request->validate([
            'session_token' => 'required|string|max:64|unique:dining_sessions,session_token,' . $id . ',session_id',
            'titular_name' => 'required|string|max:100',
            'status' => 'nullable|in:ACTIVE,PAYING,CLOSED',
            'opened_by_user_id' => 'nullable|integer|exists:users,user_id',
            'closed_at' => 'nullable|date' 
        ]);

        $sesion->session_token = $request->session_token;
        $sesion->titular_name = $request->titular_name;
        
        if ($request->has('status')) {
            $sesion->status = $request->status;
        }
        if ($request->has('opened_by_user_id')) {
            $sesion->opened_by_user_id = $request->opened_by_user_id;
        }
        if ($request->has('closed_at')) {
            $sesion->closed_at = $request->closed_at;
        }
        
        $sesion->save();
        return $sesion;
    }

    public function destroy($id) {
        $sesion = DiningSession::findOrFail($id);
        $sesion->delete();
        return ["mensaje" => "Sesion eliminada con exito", "id_borrado" => $id];
    }

    // ===== ZONA MESERO =====

    // Ver sesiones activas del mesero logueado
    public function misSesiones(Request $request) {
        $mesero = $request->user();
        
        $sesiones = DiningSession::with(['sessionTables.table', 'orders.details.product'])
            ->where('opened_by_user_id', $mesero->user_id)
            ->whereIn('status', ['ACTIVE', 'PAYING'])
            ->get();

        return response()->json([
            'mesero' => $mesero->full_name,
            'sesiones' => $sesiones
        ], 200);
    }

    // Crear nueva sesión desde el mesero
    public function crearSesionMesero(Request $request) {
        $mesero = $request->user();

        $request->validate([
            'titular_name' => 'required|string|max:100',
        ]);

        // Generar token aleatorio único para esta sesión
        $sessionToken = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        $sesion = new DiningSession();
        $sesion->session_token = $sessionToken;
        $sesion->titular_name = $request->titular_name;
        $sesion->status = 'ACTIVE';
        $sesion->opened_by_user_id = $mesero->user_id;
        $sesion->save();

        return response()->json([
            'mensaje' => 'Sesión creada exitosamente',
            'atendida_por' => $mesero->full_name,
            'sesion' => $sesion
        ], 201);
    }

    // Ver detalle de una sesión específica del mesero
    public function detalleSesionMesero(Request $request, $session_id) {
        $mesero = $request->user();
        
        $sesion = DiningSession::with(['sessionTables.table', 'orders.details.product'])
            ->find($session_id);
        
        if (!$sesion) {
            return response()->json(['mensaje' => 'Sesión no encontrada'], 404);
        }

        if ($sesion->opened_by_user_id != $mesero->user_id) {
            return response()->json(['mensaje' => 'Esta sesión pertenece a otro mesero'], 403);
        }

        return response()->json([
            'atendida_por' => $mesero->full_name,
            'sesion' => $sesion
        ], 200);
    }

    // Cerrar sesión
    public function cerrarSesionMesero(Request $request, $session_id) {
        $mesero = $request->user();
        
        $sesion = DiningSession::find($session_id);

        if (!$sesion) {
            return response()->json(['mensaje' => 'Sesión no encontrada'], 404);
        }
        if ($sesion->opened_by_user_id != $mesero->user_id) {
            return response()->json(['mensaje' => 'Esta sesión pertenece a otro mesero'], 403);
        }

        $sesion->status = 'CLOSED';
        $sesion->closed_at = now();
        $sesion->save();

        return response()->json([
            'mensaje' => 'Sesión cerrada exitosamente',
            'sesion' => $sesion
        ], 200);
    }
}