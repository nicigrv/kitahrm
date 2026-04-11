@extends('layouts.app')

@section('title', 'Neuer Benutzer')
@section('page-title', 'Neuen Benutzer anlegen')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-800">Benutzer anlegen</h2>
            <a href="/users" class="text-sm text-gray-500 hover:text-gray-700">Abbrechen</a>
        </div>

        <form method="POST" action="/users" x-data="{ role: '{{ old('role', 'KITA_MANAGER') }}' }" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       placeholder="Vor- und Nachname"
                       class="w-full px-4 py-2.5 border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-Mail <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       placeholder="benutzer@beispiel.de"
                       class="w-full px-4 py-2.5 border {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rolle <span class="text-red-500">*</span></label>
                <select id="role" name="role" x-model="role" required
                        class="w-full px-4 py-2.5 border {{ $errors->has('role') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="KITA_MANAGER" {{ old('role') === 'KITA_MANAGER' ? 'selected' : '' }}>Kita-Leitung</option>
                    <option value="KITA_STAFF" {{ old('role') === 'KITA_STAFF' ? 'selected' : '' }}>Kita-Personal</option>
                    <option value="ADMIN" {{ old('role') === 'ADMIN' ? 'selected' : '' }}>Administrator</option>
                </select>
                @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div x-show="role === 'KITA_MANAGER' || role === 'KITA_STAFF'" x-cloak>
                <label for="kita_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Einrichtung <span x-show="role !== 'ADMIN'" class="text-red-500">*</span>
                </label>
                <select id="kita_id" name="kita_id"
                        class="w-full px-4 py-2.5 border {{ $errors->has('kita_id') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Einrichtung wählen...</option>
                    @foreach($kitas as $kita)
                    <option value="{{ $kita->id }}" {{ old('kita_id') == $kita->id ? 'selected' : '' }}>{{ $kita->name }}</option>
                    @endforeach
                </select>
                @error('kita_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2 border-t border-gray-100">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Passwort setzen</h3>
                <div class="space-y-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Passwort <span class="text-red-500">*</span></label>
                        <input type="password" id="password" name="password" required minlength="8"
                               class="w-full px-4 py-2.5 border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-400">Mindestens 8 Zeichen</p>
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Passwort bestätigen <span class="text-red-500">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="/users" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Abbrechen
                </a>
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                    Benutzer anlegen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
