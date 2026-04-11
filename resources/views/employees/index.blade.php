@extends('layouts.app')

@section('title', 'Mitarbeiter')
@section('page-title', 'Mitarbeiterübersicht')

@section('content')
<div x-data="{
    search: '{{ addslashes(request('search', '')) }}',
    filterKita: '{{ request('kita_id', '') }}',
    filterStatus: '{{ request('status', '') }}',
    filterContract: '{{ request('contract', '') }}',
    sortBy: 'name',
    sortDir: 'asc',
    employees: {{ $employees->map(fn($e) => array_merge($e->toArray(), ['kita_name' => $e->kita?->name ?? '']))->values()->toJson() }},
    contractLabels: {
        'UNBEFRISTET': 'Unbefristet',
        'BEFRISTET': 'Befristet',
        'MINIJOB': 'Minijob',
        'AUSBILDUNG': 'Ausbildung',
        'PRAKTIKUM': 'Praktikum',
        'ELTERNZEIT': 'Elternzeit'
    },
    get filtered() {
        let list = this.employees;
        if (this.filterStatus) list = list.filter(e => this.filterStatus === 'active' ? e.is_active : !e.is_active);
        if (this.filterContract) list = list.filter(e => e.contract_type === this.filterContract);
        if (this.search.trim()) {
            const q = this.search.toLowerCase();
            list = list.filter(e =>
                (e.first_name + ' ' + e.last_name).toLowerCase().includes(q) ||
                (e.email || '').toLowerCase().includes(q) ||
                (e.position || '').toLowerCase().includes(q) ||
                (e.kita_name || '').toLowerCase().includes(q)
            );
        }
        list = list.slice().sort((a, b) => {
            let av = '', bv = '';
            if (this.sortBy === 'name') { av = a.last_name + a.first_name; bv = b.last_name + b.first_name; }
            else if (this.sortBy === 'kita') { av = a.kita_name; bv = b.kita_name; }
            else if (this.sortBy === 'position') { av = a.position || ''; bv = b.position || ''; }
            else if (this.sortBy === 'hours') { return this.sortDir === 'asc' ? a.weekly_hours - b.weekly_hours : b.weekly_hours - a.weekly_hours; }
            else if (this.sortBy === 'start') { av = a.start_date; bv = b.start_date; }
            return this.sortDir === 'asc' ? av.localeCompare(bv, 'de') : bv.localeCompare(av, 'de');
        });
        return list;
    },
    get stats() {
        const all = this.employees;
        const active = all.filter(e => e.is_active);
        const totalHours = active.reduce((s, e) => s + parseFloat(e.weekly_hours), 0);
        return { total: all.length, active: active.length, inactive: all.length - active.length, totalHours: totalHours.toFixed(1) };
    },
    toggleSort(col) {
        if (this.sortBy === col) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        else { this.sortBy = col; this.sortDir = 'asc'; }
    },
    initials(e) {
        return (e.first_name.charAt(0) + e.last_name.charAt(0)).toUpperCase();
    },
    contractBadge(type) {
        const map = {
            'UNBEFRISTET': 'bg-green-100 text-green-800',
            'BEFRISTET': 'bg-yellow-100 text-yellow-800',
            'MINIJOB': 'bg-blue-100 text-blue-800',
            'AUSBILDUNG': 'bg-purple-100 text-purple-800',
            'PRAKTIKUM': 'bg-orange-100 text-orange-800',
            'ELTERNZEIT': 'bg-pink-100 text-pink-800'
        };
        return map[type] || 'bg-gray-100 text-gray-700';
    }
}" class="space-y-4">

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl shadow-sm px-4 py-3 flex items-center space-x-3">
            <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900" x-text="stats.total"></div>
                <div class="text-xs text-gray-500">Gesamt</div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm px-4 py-3 flex items-center space-x-3">
            <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900" x-text="stats.active"></div>
                <div class="text-xs text-gray-500">Aktiv</div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm px-4 py-3 flex items-center space-x-3">
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900" x-text="stats.inactive"></div>
                <div class="text-xs text-gray-500">Inaktiv</div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm px-4 py-3 flex items-center space-x-3">
            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900"><span x-text="stats.totalHours"></span><span class="text-sm font-normal text-gray-400"> h</span></div>
                <div class="text-xs text-gray-500">Wochenstunden</div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-xl shadow-sm px-4 py-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <div class="flex flex-wrap gap-2 flex-1">
                <!-- Search -->
                <div class="relative min-w-[200px] flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="search" placeholder="Name, E-Mail, Position..."
                           class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Status Filter -->
                <select x-model="filterStatus"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Alle Status</option>
                    <option value="active">Aktiv</option>
                    <option value="inactive">Inaktiv</option>
                </select>

                <!-- Contract Filter -->
                <select x-model="filterContract"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Alle Verträge</option>
                    <option value="UNBEFRISTET">Unbefristet</option>
                    <option value="BEFRISTET">Befristet</option>
                    <option value="MINIJOB">Minijob</option>
                    <option value="AUSBILDUNG">Ausbildung</option>
                    <option value="PRAKTIKUM">Praktikum</option>
                    <option value="ELTERNZEIT">Elternzeit</option>
                </select>

                @if(session('user_role') === 'ADMIN')
                <!-- Kita Filter -->
                <form method="GET" action="/employees" class="contents">
                    <select name="kita_id" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Alle Kitas</option>
                        @foreach($kitas as $kita)
                        <option value="{{ $kita->id }}" {{ request('kita_id') == $kita->id ? 'selected' : '' }}>{{ $kita->name }}</option>
                        @endforeach
                    </select>
                </form>
                @endif
            </div>

            @if(session('user_role') !== 'KITA_STAFF')
            <a href="/employees/create"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex-shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Neuer Mitarbeiter
            </a>
            @endif
        </div>

        <p class="text-xs text-gray-400 mt-2">
            <span x-text="filtered.length"></span> von <span x-text="employees.length"></span> Mitarbeitern
        </p>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left">
                            <button @click="toggleSort('name')" class="flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Name
                                <svg class="ml-1 w-3 h-3 opacity-50" :class="sortBy==='name' ? 'opacity-100' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-text="sortBy==='name' && sortDir==='desc' ? 'M19 9l-7 7-7-7' : 'M5 15l7-7 7 7'" d="M5 15l7-7 7 7"/>
                                </svg>
                            </button>
                        </th>
                        @if(session('user_role') === 'ADMIN')
                        <th class="px-5 py-3 text-left">
                            <button @click="toggleSort('kita')" class="flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Kita
                                <svg class="ml-1 w-3 h-3" :class="sortBy==='kita' ? 'opacity-100' : 'opacity-30'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-text="sortBy==='kita' && sortDir==='desc' ? 'M19 9l-7 7-7-7' : 'M5 15l7-7 7 7'" d="M5 15l7-7 7 7"/>
                                </svg>
                            </button>
                        </th>
                        @endif
                        <th class="px-5 py-3 text-left">
                            <button @click="toggleSort('position')" class="flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Position
                            </button>
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vertrag</th>
                        <th class="px-5 py-3 text-left">
                            <button @click="toggleSort('hours')" class="flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Std/Wo
                            </button>
                        </th>
                        <th class="px-5 py-3 text-left">
                            <button @click="toggleSort('start')" class="flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Seit
                            </button>
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="e in filtered" :key="e.id">
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-indigo-700 text-xs font-semibold" x-text="initials(e)"></span>
                                    </div>
                                    <div>
                                        <a :href="'/employees/' + e.id"
                                           class="font-medium text-gray-900 hover:text-indigo-600 transition-colors"
                                           x-text="e.last_name + ', ' + e.first_name"></a>
                                        <div class="text-xs text-gray-400" x-text="e.email || ''"></div>
                                    </div>
                                </div>
                            </td>
                            @if(session('user_role') === 'ADMIN')
                            <td class="px-5 py-3 text-sm text-gray-600" x-text="e.kita_name || '—'"></td>
                            @endif
                            <td class="px-5 py-3 text-sm text-gray-600" x-text="e.position || '—'"></td>
                            <td class="px-5 py-3">
                                <span :class="contractBadge(e.contract_type)"
                                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                      x-text="contractLabels[e.contract_type] || e.contract_type"></span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600 tabular-nums">
                                <span x-text="parseFloat(e.weekly_hours).toFixed(1).replace('.', ',')"></span>
                                <span class="text-gray-400 text-xs">h</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 tabular-nums">
                                <span x-text="e.start_date ? new Date(e.start_date).toLocaleDateString('de-DE', {day:'2-digit', month:'2-digit', year:'numeric'}) : '—'"></span>
                            </td>
                            <td class="px-5 py-3">
                                <span :class="e.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                      x-text="e.is_active ? 'Aktiv' : 'Inaktiv'"></span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center space-x-2">
                                    <a :href="'/employees/' + e.id"
                                       class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded transition-colors">
                                        Details
                                    </a>
                                    @if(session('user_role') !== 'KITA_STAFF')
                                    <a :href="'/employees/' + e.id + '/edit'"
                                       class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded transition-colors">
                                        Bearb.
                                    </a>
                                    <form :action="'/employees/' + e.id" method="POST"
                                          @submit.prevent="if(confirm('Mitarbeiter ' + e.first_name + ' ' + e.last_name + ' wirklich löschen?')) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition-colors">
                                            Löschen
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p class="text-gray-400">Keine Mitarbeiter gefunden.</p>
                                <button @click="search=''; filterStatus=''; filterContract=''" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">
                                    Filter zurücksetzen
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
