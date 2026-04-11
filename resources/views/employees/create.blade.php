@extends('layouts.app')

@section('title', 'Neuer Mitarbeiter')
@section('page-title', 'Neuer Mitarbeiter')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">

        <!-- Form Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Mitarbeiter anlegen</h2>
                <p class="text-sm text-gray-400 mt-0.5">Pflichtfelder sind mit <span class="text-red-500">*</span> markiert</p>
            </div>
            <a href="/employees" class="text-sm text-gray-500 hover:text-gray-700">Abbrechen</a>
        </div>

        <form method="POST" action="/employees" class="divide-y divide-gray-50">
            @csrf

            <!-- Persönliche Daten -->
            <div class="px-6 py-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center">
                    <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 inline-flex items-center justify-center mr-2 text-xs font-bold">1</span>
                    Persönliche Daten
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">Vorname <span class="text-red-500">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                               class="w-full px-4 py-2.5 border {{ $errors->has('first_name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                        @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Nachname <span class="text-red-500">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                               class="w-full px-4 py-2.5 border {{ $errors->has('last_name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                        @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">Geburtsdatum</label>
                        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}"
                               class="w-full px-4 py-2.5 border {{ $errors->has('birth_date') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('birth_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="w-full px-4 py-2.5 border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               class="w-full px-4 py-2.5 border {{ $errors->has('phone') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <textarea id="address" name="address" rows="2"
                                  placeholder="Straße, PLZ Ort"
                                  class="w-full px-4 py-2.5 border {{ $errors->has('address') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address') }}</textarea>
                        @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Beschäftigung -->
            <div class="px-6 py-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center">
                    <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 inline-flex items-center justify-center mr-2 text-xs font-bold">2</span>
                    Beschäftigung
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position / Funktion</label>
                        <input type="text" id="position" name="position" value="{{ old('position') }}"
                               placeholder="z.B. Erzieherin, Kinderpfleger, Leitung"
                               class="w-full px-4 py-2.5 border {{ $errors->has('position') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('position') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="kita_id" class="block text-sm font-medium text-gray-700 mb-1">Einrichtung <span class="text-red-500">*</span></label>
                        @if(session('user_role') === 'ADMIN')
                        <select id="kita_id" name="kita_id" required
                                class="w-full px-4 py-2.5 border {{ $errors->has('kita_id') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">Bitte wählen...</option>
                            @foreach($kitas as $kita)
                            <option value="{{ $kita->id }}" {{ old('kita_id') == $kita->id ? 'selected' : '' }}>{{ $kita->name }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="hidden" name="kita_id" value="{{ session('user_kita_id') }}">
                        <input type="text" value="{{ $kitas->first()->name ?? '' }}" disabled
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                        @endif
                        @error('kita_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Eintrittsdatum <span class="text-red-500">*</span></label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                               class="w-full px-4 py-2.5 border {{ $errors->has('start_date') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('start_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Austrittsdatum</label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                               class="w-full px-4 py-2.5 border {{ $errors->has('end_date') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('end_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contract_type" class="block text-sm font-medium text-gray-700 mb-1">Vertragsart <span class="text-red-500">*</span></label>
                        <select id="contract_type" name="contract_type" required
                                class="w-full px-4 py-2.5 border {{ $errors->has('contract_type') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">Bitte wählen...</option>
                            @foreach($contractTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('contract_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('contract_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="weekly_hours" class="block text-sm font-medium text-gray-700 mb-1">Wochenstunden <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" id="weekly_hours" name="weekly_hours" value="{{ old('weekly_hours', '39.0') }}"
                                   step="0.5" min="0" max="80" required
                                   class="w-full px-4 py-2.5 pr-10 border {{ $errors->has('weekly_hours') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">h</span>
                        </div>
                        @error('weekly_hours') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">Mitarbeiter ist aktiv (Beschäftigung läuft)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Notizen -->
            <div class="px-6 py-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center">
                    <span class="w-5 h-5 rounded bg-indigo-100 text-indigo-600 inline-flex items-center justify-center mr-2 text-xs font-bold">3</span>
                    Notizen (optional)
                </h3>
                <textarea id="notes" name="notes" rows="3"
                          placeholder="Interne Anmerkungen zum Mitarbeiter..."
                          class="w-full px-4 py-2.5 border {{ $errors->has('notes') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
                <a href="/employees" class="text-sm text-gray-500 hover:text-gray-700">Abbrechen</a>
                <div class="flex space-x-3">
                    <button type="submit"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                        Mitarbeiter anlegen
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
