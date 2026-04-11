@extends('layouts.app')

@section('title', 'Kita bearbeiten')
@section('page-title', 'Kita bearbeiten')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-800">{{ $kita->name }} bearbeiten</h2>
            <a href="/kitas/{{ $kita->id }}" class="text-sm text-gray-500 hover:text-gray-700">Abbrechen</a>
        </div>

        <form method="POST" action="/kitas/{{ $kita->id }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Name -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $kita->name) }}" required
                           class="w-full px-4 py-2.5 border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Short Code -->
                <div>
                    <label for="short_code" class="block text-sm font-medium text-gray-700 mb-1">Kurzcode <span class="text-red-500">*</span></label>
                    <input type="text" id="short_code" name="short_code" value="{{ old('short_code', $kita->short_code) }}" required maxlength="20"
                           class="w-full px-4 py-2.5 border {{ $errors->has('short_code') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('short_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Min First Aid -->
                <div>
                    <label for="min_first_aid" class="block text-sm font-medium text-gray-700 mb-1">Min. Ersthelfer <span class="text-red-500">*</span></label>
                    <input type="number" id="min_first_aid" name="min_first_aid" value="{{ old('min_first_aid', $kita->min_first_aid) }}" required min="0"
                           class="w-full px-4 py-2.5 border {{ $errors->has('min_first_aid') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('min_first_aid') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Address -->
                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <textarea id="address" name="address" rows="2"
                              class="w-full px-4 py-2.5 border {{ $errors->has('address') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address', $kita->address) }}</textarea>
                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $kita->phone) }}"
                           class="w-full px-4 py-2.5 border {{ $errors->has('phone') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $kita->email) }}"
                           class="w-full px-4 py-2.5 border {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="/kitas/{{ $kita->id }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Abbrechen
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
