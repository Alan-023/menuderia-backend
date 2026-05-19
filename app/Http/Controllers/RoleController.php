<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function index() {
        return Role::all();
    }

    public function store(Request $request) {
        // EL CADENERO
        $request->validate([
            'role_name' => 'required|string|max:50|unique:roles,role_name',
            'description' => 'nullable|string|max:255'
        ]);

        $rol = new Role();
        $rol->role_name = $request->role_name;
        $rol->description = $request->description;
        $rol->save();
        
        return $rol;
    }

    public function show($id) {
        // EL SEGURO
        return Role::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $rol = Role::findOrFail($id);

        $request->validate([
            'role_name' => 'required|string|max:50|unique:roles,role_name,' . $id . ',role_id',
            'description' => 'nullable|string|max:255'
        ]);

        $rol->role_name = $request->role_name;
        $rol->description = $request->description;
        $rol->save();
        
        return $rol;
    }

    public function destroy($id) {
        $rol = Role::findOrFail($id);
        $rol->delete();
        return ["mensaje" => "Rol eliminado con exito", "id_borrado" => $id];
    }
}