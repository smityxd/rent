<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Credenciales incorrectas'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function showLogin(Request $request)
    {
        return view('auth.login');
    }

    public function register(Request $request)
    {
        // Validar datos del formulario
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email|max:100',
            'password' => 'required|string|min:6',
            'password2' => 'required|string|min:6'
        ]);

        if($validated['password'] != $validated['password']){
            return back()->withErrors(['general' => 'Las contraseñas no coinciden']);
        }

        // Insertar usuario en la base de datos
        $usuarioId = DB::table('usuarios')->insertGetId([
            'nombres' => $validated['nombres'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Verificar si la inserción fue exitosa
        if (!$usuarioId) {
            return back()->withErrors(['general' => 'No se pudo registrar el usuario. Inténtelo de nuevo.']);
        }

        // Intentar loguear al usuario automáticamente
        if (Auth::attempt($validated)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        // Si el login falla, redirigir con mensaje de error
        return back()->withErrors(['correo' => 'Registro exitoso, pero no se pudo iniciar sesión.']);
    }

    public function showRegister(Request $request)
    {
        return view('auth.register');
    }
}
