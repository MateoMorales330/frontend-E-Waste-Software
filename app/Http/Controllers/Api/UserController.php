<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(\App\Models\User::all([
            'success' => true,
            'data' => User::all(),
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $token = $user->createToken('frontend')->plainTextToken;

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'success' => true,
            'data' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'tipo' => 'required|in:usuario,dueño',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Correo o contraseña incorrectos',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
            'token' => $user->createToken('frontend')->plainTextToken,
        ]);
        if ($credentials['tipo'] === 'dueño') {
            $dueno = Dueno::where('email', $credentials['email'])->first();

            if (!$dueno || !Hash::check($credentials['password'], $dueno->password)) {
                return response()->json([
                    'message' => 'Correo o contraseña incorrectos',
                ], 401);
            }
            $token = $dueno->createToken('auth_token', ['role:dueño'])->plainTextToken;
            return response()->json(['token' => $token, 'tipo' => 'dueño', 'datos' => $dueno]);
            return response()->json([
                'success' => true,
                'data' => $dueno,
                'token' => $dueno->createToken('frontend')->plainTextToken,
            ]);
        }
    }
}