@extends('layouts.app')

@section('title', 'Kita bearbeiten')
@section('page-title', 'Kita bearbeiten')

@section('content')
<div class="max-w-2xl space-y-5">

    <!-- Main Form -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-800">{{ $kita->name }}</h2>
            <a href="/kitas/{{ $kita->id }}" class="text-sm text-gray-500 hover:text-gray-700">Abbrechen</a>
        </div>

        <form method="POST" action="/kitas/{{ $kita->id }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Stammdaten -->
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Stammdaten</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name der Einrichtung <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $kita->name) }}" required
                               class="w-full px-4 py-2.5 border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="short_code" class="block text-sm font-medium text-gray-700 mb-1">Kurzcode <span class="text-red-500">*</span></label>
                        <input type="text" id="short_code" name="short_code" value="{{ old('short_code', $kita->short_code) }}" required maxlength="20"
                               class="w-full px-4 py-2.5 border {{ $errors->has('short_code') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('short_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $kita->email) }}"
                               class="w-full px-4 py-2.5 border {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $kita->phone) }}"
                               class="w-full px-4 py-2.5 border {{ $errors->has('phone') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <textarea id="address" name="address" rows="2"
                                  class="w-full px-4 py-2.5 border {{ $errors->has('address') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address', $kita->address) }}</textarea>
                        @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Personalanforderungen -->
            <div class="pt-4 border-t border-gray-100">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Personalanforderungen</h3>
                <p class="text-xs text-gray-400 mb-4">Mindestbesetzung für diese Einrichtung (0 = keine Vorgabe)</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label for="min_staff_total" class="block text-sm font-medium text-gray-700 mb-1">Gesamtpersonal (min.)</label>
                        <input type="number" id="min_staff_total" name="min_staff_total" value="{{ old('min_staff_total', $kita->min_staff_total) }}" min="0"
                               class="w-full px-4 py-2.5 border {{ $errors->has('min_staff_total') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('min_staff_total') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="min_skilled_staff" class="block text-sm font-medium text-gray-700 mb-1">Fachkräfte (min.)</label>
                        <input type="number" id="min_skilled_staff" name="min_skilled_staff" value="{{ old('min_skilled_staff', $kita->min_skilled_staff) }}" min="0"
                               class="w-full px-4 py-2.5 border {{ $errors->has('min_skilled_staff') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('min_skilled_staff') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="min_first_aid" class="block text-sm font-medium text-gray-700 mb-1">Ersthelfer (min.) <span class="text-red-500">*</span></label>
                        <input type="number" id="min_first_aid" name="min_first_aid" value="{{ old('min_first_aid', $kita->min_first_aid) }}" min="0" required
                               class="w-full px-4 py-2.5 border {{ $errors->has('min_first_aid') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('min_first_aid') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="target_weekly_hours" class="block text-sm font-medium text-gray-700 mb-1">Soll-Wochenstunden</label>
                        <div class="relative max-w-xs">
                            <input type="number" id="target_weekly_hours" name="target_weekly_hours"
                                   value="{{ old('target_weekly_hours', $kita->target_weekly_hours) }}" min="0" step="0.5"
                                   class="w-full px-4 py-2.5 pr-10 border {{ $errors->has('target_weekly_hours') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 pointer-events-none">h</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Gesamte Soll-Wochenstunden aller aktiven Mitarbeiter (0 = keine Vorgabe)</p>
                        @error('target_weekly_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Schulungsanforderungen -->
            @if($allCategories->count() > 0)
            <div class="pt-4 border-t border-gray-100">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Schulungsanforderungen</h3>
                <p class="text-xs text-gray-400 mb-4">Mindestanzahl zertifizierter Mitarbeiter je Schulungskategorie (0 = keine Vorgabe)</p>
                <div class="space-y-3">
                    @foreach($allCategories as $cat)
                    @php $req = $trainingRequirements->get($cat->id); @endphp
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="text-sm font-medium text-gray-800">{{ $cat->name }}</span>
                            @if($cat->is_first_aid)
                            <span class="ml-2 px-1.5 py-0.5 text-xs bg-red-100 text-red-700 rounded">Erste Hilfe</span>
                            @endif
                            @if($cat->validity_months)
                            <span class="ml-1 text-xs text-gray-400">{{ $cat->validity_months }} Mon. gültig</span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="text-xs text-gray-500">Min. Personen:</label>
                            <input type="number"
                                   name="training_requirements[{{ $cat->id }}]"
                                   value="{{ old('training_requirements.' . $cat->id, $req ? $req->min_count : 0) }}"
                                   min="0" max="99"
                                   class="w-16 px-2 py-1 text-sm text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Notizen -->
            <div class="pt-4 border-t border-gray-100">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notizen</label>
                <textarea id="notes" name="notes" rows="3"
                          class="w-full px-4 py-2.5 border {{ $errors->has('notes') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $kita->notes) }}</textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="/kitas/{{ $kita->id }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Abbrechen
                </a>
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                    Änderungen speichern
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
