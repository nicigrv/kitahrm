@extends('layouts.app')

@section('title', 'Neuer Mitarbeiter')
@section('page-title', 'Neuer Mitarbeiter')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-800">Mitarbeiter anlegen</h2>
            <a href="/employees" class="text-sm text-gray-500 hover:text-gray-700">Abbrechen</a>
        </div>

        <form method="POST" action="/employees" class="space-y-6">
            @csrf

            <!-- Persönliche Daten -->
            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-4">Persönliche Daten</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">Vorname <span class="text-red-500">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                               class="w-full px-4 py-2.5 border {{ $errors->has('first_name') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Nachname <span class="text-red-500">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                               class="w-full px-4 py-2.5 border {{ $errors->has('last_name') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">Geburtsdatum</label>
                        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}"
                               class="w-full px-4 py-2.5 border {{ $errors->has('birth_date') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('birth_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="w-full px-4 py-2.5 border {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               class="w-full px-4 py-2.5 border {{ $errors->has('phone') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <textarea id="address" name="address" rows="2"
                                  class="w-full px-4 py-2.5 border {{ $errors->has('address') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address') }}</textarea>
                        @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Beschäftigung -->
            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-4">Beschäftigung</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position / Funktion</label>
                        <input type="text" id="position" name="position" value="{{ old('position') }}" placeholder="z.B. Erzieherin"
                               class="w-full px-4 py-2.5 border {{ $errors->has('position') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('position') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="kita_id" class="block text-sm font-medium text-gray-700 mb-1">Kita <span class="text-red-500">*</span></label>
                        @if(session('user_role') === 'ADMIN')
                        <select id="kita_id" name="kita_id" required
                                class="w-full px-4 py-2.5 border {{ $errors->has('kita_id') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">Bitte wählen...</option>
                            @foreach($kitas as $kita)
                            <option value="{{ $kita->id }}" {{ old('kita_id') == $kita->id ? 'selected' : '' }}>{{ $kita->name }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="hidden" name="kita_id" value="{{ session('user_kita_id') }}">
                        <input type="text" value="{{ $kitas->first()->name ?? '' }}" disabled
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                        @endif
                        @error('kita_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Eintrittsdatum <span class="text-red-500">*</span></label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                               class="w-full px-4 py-2.5 border {{ $errors->has('start_date') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Austrittsdatum</label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                               class="w-full px-4 py-2.5 border {{ $errors->has('end_date') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contract_type" class="block text-sm font-medium text-gray-700 mb-1">Vertragsart <span class="text-red-500">*</span></label>
                        <select id="contract_type" name="contract_type" required
                                class="w-full px-4 py-2.5 border {{ $errors->has('contract_type') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">Bitte wählen...</option>
                            @foreach($contractTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('contract_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('contract_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="weekly_hours" class="block text-sm font-medium text-gray-700 mb-1">Wochenstunden <span class="text-red-500">*</span></label>
                        <input type="number" id="weekly_hours" name="weekly_hours" value="{{ old('weekly_hours', '39.0') }}" step="0.5" min="0" max="80" required
                               class="w-full px-4 py-2.5 border {{ $errors->has('weekly_hours') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('weekly_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2 flex items-center space-x-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_active" class="text-sm font-medium text-gray-700">Mitarbeiter ist aktiv</label>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-4">Notizen</h3>
                <textarea id="notes" name="notes" rows="3" placeholder="Interne Notizen zum Mitarbeiter..."
                          class="w-full px-4 py-2.5 border {{ $errors->has('notes') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="/employees"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Abbrechen
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                    Mitarbeiter anlegen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
