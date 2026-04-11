@extends('layouts.app')

@section('title', isset($completion) ? 'Schulung bearbeiten' : 'Schulung eintragen')
@section('page-title', isset($completion) ? 'Schulung bearbeiten' : 'Schulung eintragen')

@section('content')
<div class="max-w-2xl" x-data="{
    categoryId: '{{ old('category_id', isset($completion) ? $completion->category_id : request('category_id', '')) }}',
    completedDate: '{{ old('completed_date', isset($completion) ? $completion->completed_date?->format('Y-m-d') : '') }}',
    validityMonths: null,
    categories: {{ $categories->toJson() }},
    get selectedCategory() {
        return this.categories.find(c => c.id == this.categoryId);
    },
    autoCalcExpiry() {
        if (this.selectedCategory && this.selectedCategory.validity_months && this.completedDate) {
            const d = new Date(this.completedDate);
            d.setMonth(d.getMonth() + parseInt(this.selectedCategory.validity_months));
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            document.getElementById('expiry_date').value = year + '-' + month + '-' + day;
        }
    }
}">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    {{ isset($completion) ? 'Schulung bearbeiten' : 'Schulung eintragen' }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Mitarbeiter: <strong>{{ $employee->full_name }}</strong>
                </p>
            </div>
            <a href="/employees/{{ $employee->id }}" class="text-sm text-gray-500 hover:text-gray-700">Abbrechen</a>
        </div>

        <form method="POST"
              action="{{ isset($completion) ? '/training/completions/' . $completion->id : '/training/completions' }}"
              enctype="multipart/form-data"
              class="space-y-5">
            @csrf
            @if(isset($completion))
            @method('PUT')
            @else
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            @endif

            <!-- Category -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Schulungskategorie <span class="text-red-500">*</span>
                </label>
                <select id="category_id" name="category_id" required
                        x-model="categoryId"
                        @change="autoCalcExpiry()"
                        class="w-full px-4 py-2.5 border {{ $errors->has('category_id') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Bitte wählen...</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (old('category_id', isset($completion) ? $completion->category_id : request('category_id')) == $cat->id) ? 'selected' : '' }}>
                        {{ $cat->name }}{{ $cat->validity_months ? ' (' . $cat->validity_months . ' Monate)' : ' (unbegrenzt)' }}
                        {{ $cat->is_first_aid ? ' – Erste Hilfe' : '' }}
                    </option>
                    @endforeach
                </select>
                @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <template x-if="selectedCategory && selectedCategory.description">
                    <p class="mt-1.5 text-xs text-gray-500" x-text="selectedCategory.description"></p>
                </template>
            </div>

            <!-- Completed Date -->
            <div>
                <label for="completed_date" class="block text-sm font-medium text-gray-700 mb-1">
                    Abschlussdatum <span class="text-red-500">*</span>
                </label>
                <input type="date" id="completed_date" name="completed_date"
                       value="{{ old('completed_date', isset($completion) ? $completion->completed_date?->format('Y-m-d') : '') }}"
                       required
                       x-model="completedDate"
                       @change="autoCalcExpiry()"
                       class="w-full px-4 py-2.5 border {{ $errors->has('completed_date') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('completed_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Expiry Date -->
            <div>
                <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">
                    Ablaufdatum
                    <span class="text-xs text-gray-400 font-normal">(wird automatisch berechnet wenn Kategorie eine Gültigkeitsdauer hat)</span>
                </label>
                <input type="date" id="expiry_date" name="expiry_date"
                       value="{{ old('expiry_date', isset($completion) ? $completion->expiry_date?->format('Y-m-d') : '') }}"
                       class="w-full px-4 py-2.5 border {{ $errors->has('expiry_date') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('expiry_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">Leer lassen = kein Ablaufdatum (dauerhaft gültig)</p>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notizen</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Optionale Anmerkungen zur Schulung..."
                          class="w-full px-4 py-2.5 border {{ $errors->has('notes') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', isset($completion) ? $completion->notes : '') }}</textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Certificate Upload -->
            <div>
                <label for="certificate" class="block text-sm font-medium text-gray-700 mb-1">
                    Zertifikat hochladen
                    <span class="text-xs text-gray-400 font-normal">(PDF, JPG, PNG – max. 10 MB)</span>
                </label>
                @if(isset($completion) && $completion->certificate_path)
                <div class="mb-2 p-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Zertifikat vorhanden. Neue Datei hochladen um zu ersetzen.</span>
                </div>
                @endif
                <input type="file" id="certificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-3 py-2.5 border {{ $errors->has('certificate') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('certificate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="/employees/{{ $employee->id }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Abbrechen
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                    {{ isset($completion) ? 'Änderungen speichern' : 'Schulung eintragen' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
