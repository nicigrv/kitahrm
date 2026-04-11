<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kita;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with('kita')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $kitas = Kita::orderBy('name')->get();
        return view('users.create', compact('kitas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:ADMIN,KITA_MANAGER,KITA_STAFF',
            'kita_id'  => 'nullable|exists:kitas,id',
        ], [
            'name.required'      => 'Name ist erforderlich.',
            'email.required'     => 'E-Mail ist erforderlich.',
            'email.unique'       => 'Diese E-Mail-Adresse wird bereits verwendet.',
            'password.required'  => 'Passwort ist erforderlich.',
            'password.min'       => 'Passwort muss mindestens 8 Zeichen lang sein.',
            'password.confirmed' => 'Passwort-Bestätigung stimmt nicht überein.',
            'role.required'      => 'Rolle ist erforderlich.',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => password_hash($request->password, PASSWORD_BCRYPT),
            'role'     => $request->role,
            'kita_id'  => in_array($request->role, ['KITA_MANAGER', 'KITA_STAFF']) ? $request->kita_id : null,
        ]);

        return redirect()->route('users.index')->with('success', 'Benutzer wurde erfolgreich angelegt.');
    }

    public function edit(User $user)
    {
        $kitas = Kita::orderBy('name')->get();
        return view('users.edit', compact('user', 'kitas'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|in:ADMIN,KITA_MANAGER,KITA_STAFF',
            'kita_id'  => 'nullable|exists:kitas,id',
        ], [
            'name.required'      => 'Name ist erforderlich.',
            'email.required'     => 'E-Mail ist erforderlich.',
            'email.unique'       => 'Diese E-Mail-Adresse wird bereits verwendet.',
            'password.min'       => 'Passwort muss mindestens 8 Zeichen lang sein.',
            'password.confirmed' => 'Passwort-Bestätigung stimmt nicht überein.',
            'role.required'      => 'Rolle ist erforderlich.',
        ]);

        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'role'    => $request->role,
            'kita_id' => in_array($request->role, ['KITA_MANAGER', 'KITA_STAFF']) ? $request->kita_id : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = password_hash($request->password, PASSWORD_BCRYPT);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Benutzer wurde erfolgreich aktualisiert.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === session('user_id')) {
            return back()->with('error', 'Sie können Ihren eigenen Account nicht löschen.');
        }
        $name = $user->name;
        $user->delete();
        return redirect()->route('users.index')->with('success', "Benutzer \"{$name}\" wurde gelöscht.");
    }
}
