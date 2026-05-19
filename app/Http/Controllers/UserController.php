<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Hash; 

class UserController extends Controller
{
    public function index() {
        return User::all();
    }

    public function store(Request $request) {
        
        $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email', 
            'password_hash' => 'required|string|min:4',
            'role_id' => 'required|integer'
        ]);

        $usuario = new User();
        $usuario->full_name = $request->full_name;
        $usuario->email = $request->email;
        $usuario->password_hash = Hash::make($request->password_hash);
        $usuario->role_id = $request->role_id;
        $usuario->save();
        
        return $usuario;
    }

    public function show($id) {
        
        return User::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $usuario = User::findOrFail($id);

        // Validamos la actualizacion (el correo puede ser el mismo del usuario actual, por eso la regla cambia un poco)
        $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id . ',user_id', 
            'role_id' => 'required|integer'
        ]);

        $usuario->full_name = $request->full_name;
        $usuario->email = $request->email;
        
        if ($request->has('password_hash') && !empty($request->password_hash)) {
            $usuario->password_hash = Hash::make($request->password_hash);
        }
        
        $usuario->role_id = $request->role_id;
        $usuario->save();
        
        return $usuario;
    }

    public function destroy($id) {
        $usuario = User::findOrFail($id);
        $usuario->delete();
        return ["mensaje" => "Usuario eliminado con exito", "id_borrado" => $id];
    }
}