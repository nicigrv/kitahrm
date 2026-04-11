@extends('layouts.app')

@section('title', 'Mitarbeiter')
@section('page-title', 'Mitarbeiterübersicht')

@section('content')
<div x-data="{
    search: '{{ request('search') }}',
    employees: {{ $employees->toJson() }},
    get filtered() {
        if (!this.search.trim()) return this.employees;
        const q = this.search.toLowerCase();
        return this.employees.filter(e =>
            (e.first_name + ' ' + e.last_name).toLowerCase().includes(q) ||
            (e.email || '').toLowerCase().includes(q) ||
            (e.position || '').toLowerCase().includes(q)
        );
    }
}" class="space-y-4">

    <!-- Filters & Actions -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-3 flex-1">
            <!-- Search -->
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       x-model="search"
                       placeholder="Suchen (Name, E-Mail, Position)..."
                       class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            @if(session('user_role') === 'ADMIN')
            <!-- Kita Filter -->
            <form method="GET" action="/employees" class="flex gap-2">
                <select name="kita_id" onchange="this.form.submit()"
                        class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Alle Kitas</option>
                    @foreach($kitas as $kita)
                    <option value="{{ $kita->id }}" {{ request('kita_id') == $kita->id ? 'selected' : '' }}>{{ $kita->name }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()"
                        class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Alle Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktiv</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inaktiv</option>
                </select>
            </form>
            @endif
        </div>

        @if(session('user_role') !== 'KITA_STAFF')
        <a href="/employees/create"
           class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex-shrink-0">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Neuer Mitarbeiter
        </a>
        @endif
    </div>

    <!-- Results count -->
    <p class="text-sm text-gray-500">
        <span x-text="filtered.length"></span> Mitarbeiter
    </p>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        @if(session('user_role') === 'ADMIN')
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kita</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vertragsart</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Wochenstunden</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Eintrittsdatum</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="employee in filtered" :key="employee.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a :href="'/employees/' + employee.id" class="font-medium text-indigo-600 hover:text-indigo-800"
                                   x-text="employee.first_name + ' ' + employee.last_name"></a>
                                <p class="text-xs text-gray-400 mt-0.5" x-text="employee.email || ''"></p>
                            </td>
                            @if(session('user_role') === 'ADMIN')
                            <td class="px-6 py-4 text-gray-600">
                                <span x-text="employee.kita ? employee.kita.name : '-'"></span>
                            </td>
                            @endif
                            <td class="px-6 py-4 text-gray-600" x-text="employee.position || '-'"></td>
                            <td class="px-6 py-4 text-gray-600">
                                <span x-text="({
                                    'UNBEFRISTET': 'Unbefristet',
                                    'BEFRISTET': 'Befristet',
                                    'MINIJOB': 'Minijob',
                                    'AUSBILDUNG': 'Ausbildung',
                                    'PRAKTIKUM': 'Praktikum',
                                    'ELTERNZEIT': 'Elternzeit'
                                })[employee.contract_type] || employee.contract_type"></span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <span x-text="parseFloat(employee.weekly_hours).toFixed(1).replace('.', ',') + ' h'"></span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <span x-text="employee.start_date ? new Date(employee.start_date).toLocaleDateString('de-DE') : '-'"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="employee.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      x-text="employee.is_active ? 'Aktiv' : 'Inaktiv'"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a :href="'/employees/' + employee.id" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Details</a>
                                    @if(session('user_role') !== 'KITA_STAFF')
                                    <span class="text-gray-300">|</span>
                                    <a :href="'/employees/' + employee.id + '/edit'" class="text-gray-600 hover:text-gray-800 text-xs font-medium">Bearb.</a>
                                    <span class="text-gray-300">|</span>
                                    <form :action="'/employees/' + employee.id" method="POST" @submit.prevent="if(confirm('Mitarbeiter wirklich löschen?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Löschen</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                                Keine Mitarbeiter gefunden.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
