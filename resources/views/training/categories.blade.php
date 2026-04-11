@extends('layouts.app')

@section('title', 'Schulungskategorien')
@section('page-title', 'Schulungskategorien')

@section('content')
<div x-data="{
    showAddForm: false,
    editId: null,
    editData: {},
    openEdit(cat) {
        this.editId = cat.id;
        this.editData = { ...cat };
    },
    closeEdit() {
        this.editId = null;
        this.editData = {};
    }
}" class="space-y-4">

    <!-- Add New Button -->
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">{{ $categories->count() }} Kategorie(n) vorhanden</p>
        <button @click="showAddForm = !showAddForm"
                :class="showAddForm ? 'bg-gray-600 hover:bg-gray-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                class="inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span x-text="showAddForm ? 'Abbrechen' : 'Neue Kategorie'"></span>
        </button>
    </div>

    <!-- Add Form -->
    <div x-show="showAddForm" x-cloak class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Neue Schulungskategorie anlegen</h3>
        <form method="POST" action="/training/categories" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="validity_months" class="block text-sm font-medium text-gray-700 mb-1">Gültigkeitsdauer (Monate)</label>
                    <input type="number" id="validity_months" name="validity_months" value="{{ old('validity_months') }}" min="1" max="240"
                           placeholder="Leer = unbegrenzt"
                           class="w-full px-4 py-2.5 border {{ $errors->has('validity_months') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('validity_months') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Beschreibung</label>
                    <textarea id="description" name="description" rows="2"
                              class="w-full px-4 py-2.5 border {{ $errors->has('description') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sortierung</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="w-full px-4 py-2.5 border {{ $errors->has('sort_order') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col justify-end space-y-2">
                    <div class="flex items-center space-x-3">
                        <input type="hidden" name="is_first_aid" value="0">
                        <input type="checkbox" id="is_first_aid" name="is_first_aid" value="1" {{ old('is_first_aid') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_first_aid" class="text-sm font-medium text-gray-700">Erste-Hilfe-Schulung</label>
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" @click="showAddForm = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Abbrechen
                </button>
                <button type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                    Kategorie anlegen
                </button>
            </div>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Beschreibung</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Gültigkeit</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Erste Hilfe</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <!-- View Mode -->
                        <template x-if="editId !== {{ $category->id }}">
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-800">{{ $category->name }}</span>
                            </td>
                        </template>
                        <template x-if="editId !== {{ $category->id }}">
                            <td class="px-6 py-4 text-gray-500 max-w-xs truncate">{{ $category->description ?? '-' }}</td>
                        </template>
                        <template x-if="editId !== {{ $category->id }}">
                            <td class="px-6 py-4 text-center text-gray-600">
                                {{ $category->validity_months ? $category->validity_months . ' Monate' : 'Unbegrenzt' }}
                            </td>
                        </template>
                        <template x-if="editId !== {{ $category->id }}">
                            <td class="px-6 py-4 text-center">
                                @if($category->is_first_aid)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Ja</span>
                                @else
                                <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                        </template>
                        <template x-if="editId !== {{ $category->id }}">
                            <td class="px-6 py-4 text-center">
                                @if($category->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktiv</span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inaktiv</span>
                                @endif
                            </td>
                        </template>
                        <template x-if="editId !== {{ $category->id }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button @click="openEdit({{ $category->toJson() }})"
                                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Bearbeiten</button>
                                    <span class="text-gray-300">|</span>
                                    <form action="/training/categories/{{ $category->id }}" method="POST"
                                          onsubmit="return confirm('Kategorie wirklich löschen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Löschen</button>
                                    </form>
                                </div>
                            </td>
                        </template>

                        <!-- Edit Mode (inline) -->
                        <template x-if="editId === {{ $category->id }}">
                            <td colspan="6" class="px-6 py-4 bg-indigo-50">
                                <form method="POST" action="/training/categories/{{ $category->id }}" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Name *</label>
                                            <input type="text" name="name" x-model="editData.name" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Gültigkeit (Monate)</label>
                                            <input type="number" name="validity_months" x-model="editData.validity_months" min="1" max="240"
                                                   placeholder="Leer = unbegrenzt"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Sortierung</label>
                                            <input type="number" name="sort_order" x-model="editData.sort_order" min="0"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Beschreibung</label>
                                            <input type="text" name="description" x-model="editData.description"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        <div class="flex flex-col space-y-2 justify-end">
                                            <div class="flex items-center space-x-2">
                                                <input type="hidden" name="is_first_aid" value="0">
                                                <input type="checkbox" name="is_first_aid" value="1" :checked="editData.is_first_aid"
                                                       @change="editData.is_first_aid = $event.target.checked"
                                                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded">
                                                <span class="text-xs text-gray-700">Erste Hilfe</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="checkbox" name="is_active" value="1" :checked="editData.is_active"
                                                       @change="editData.is_active = $event.target.checked"
                                                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded">
                                                <span class="text-xs text-gray-700">Aktiv</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button type="submit"
                                                class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                                            Speichern
                                        </button>
                                        <button type="button" @click="closeEdit()"
                                                class="px-4 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg transition-colors">
                                            Abbrechen
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </template>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                            Noch keine Schulungskategorien angelegt.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
