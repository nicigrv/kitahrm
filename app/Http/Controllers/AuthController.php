<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('user_id')) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'E-Mail-Adresse ist erforderlich.',
            'email.email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
            'password.required' => 'Passwort ist erforderlich.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Die eingegebenen Anmeldedaten sind nicht korrekt.',
            ])->withInput($request->only('email'));
        }

        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'user_kita_id' => $user->kita_id,
        ]);

        return redirect('/dashboard')->with('success', 'Willkommen zurück, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        session()->flush();
        return redirect('/login')->with('success', 'Sie wurden erfolgreich abgemeldet.');
    }
}
