<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password_hash' => 'required|string|min:6', //el minimo
            'role_id' => 'required|integer'
        ]);

        $user = new User();
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->password_hash = Hash::make($request->password_hash); //el hash
        $user->role_id = $request->role_id;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Usuario registrado con exito',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    // login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password_hash' => 'required|string'
        ]);

        // por correo
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password_hash, $user->password_hash)) {
            return response()->json([
                'mensaje' => 'Acceso denegado. Correo o contraseña incorrectos.'
            ], 401); 
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Bienvenido, login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role_id' => $user->role_id,
            'full_name' => $user->full_name
        ]);
    }

    public function logout(Request $request)
    {
        // borramos el token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'mensaje' => 'Cierre de sesion exitoso. Token invalidado.'
        ]);
    }
}