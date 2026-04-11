@extends('layouts.app')

@section('title', $kita->name)
@section('page-title', $kita->name)

@section('content')
<div class="space-y-6">

    <!-- Kita Info Card -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-start">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <h2 class="text-xl font-bold text-gray-900">{{ $kita->name }}</h2>
                    <span class="px-2 py-1 text-xs font-mono font-medium bg-indigo-100 text-indigo-700 rounded">{{ $kita->short_code }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    @if($kita->address)
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm text-gray-600">{{ $kita->address }}</span>
                    </div>
                    @endif
                    @if($kita->phone)
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="text-sm text-gray-600">{{ $kita->phone }}</span>
                    </div>
                    @endif
                    @if($kita->email)
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm text-gray-600">{{ $kita->email }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if(session('user_role') === 'ADMIN')
            <a href="/kitas/{{ $kita->id }}/edit"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Bearbeiten
            </a>
            @endif
        </div>

        <!-- Stats Row -->
        <div class="mt-6 pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4">

            <!-- First Aid Status -->
            <div class="flex items-center space-x-3 p-3 rounded-lg {{ $firstAidOk ? 'bg-green-50' : 'bg-red-50' }}">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 {{ $firstAidOk ? 'bg-green-100' : 'bg-red-100' }}">
                    @if($firstAidOk)
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    @else
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    @endif
                </div>
                <div>
                    <p class="font-medium text-sm {{ $firstAidOk ? 'text-green-800' : 'text-red-800' }}">
                        Erste-Hilfe: {{ $firstAidOk ? 'Ausreichend' : 'Nicht ausreichend' }}
                    </p>
                    <p class="text-xs text-gray-500">{{ $firstAidCount }} / {{ $kita->min_first_aid }} Ersthelfer</p>
                </div>
            </div>

            <!-- Weekly Hours Status -->
            @if($targetHours > 0)
            <div class="p-3 rounded-lg {{ $hoursOk ? 'bg-green-50' : 'bg-amber-50' }}">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-sm font-medium {{ $hoursOk ? 'text-green-800' : 'text-amber-800' }}">
                        Wochenstunden
                    </span>
                    <span class="text-sm font-bold {{ $hoursOk ? 'text-green-700' : 'text-amber-700' }}">
                        {{ number_format($actualHours, 1, ',', '.') }} / {{ number_format($targetHours, 1, ',', '.') }} h
                    </span>
                </div>
                <div class="h-2 bg-white rounded-full overflow-hidden">
                    @php $pct = min(100, $targetHours > 0 ? ($actualHours / $targetHours * 100) : 0); @endphp
                    <div class="h-full rounded-full {{ $hoursOk ? 'bg-green-500' : 'bg-amber-400' }}"
                         style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $hoursOk ? 'Soll erfüllt' : 'Unterbesetzt' }}</p>
            </div>
            @else
            <div class="p-3 rounded-lg bg-gray-50">
                <p class="text-sm font-medium text-gray-600">Wochenstunden</p>
                <p class="text-xl font-bold text-gray-800 mt-0.5">{{ number_format($actualHours, 1, ',', '.') }} h</p>
                <p class="text-xs text-gray-400 mt-1">Kein Soll definiert</p>
            </div>
            @endif
        </div>

        <!-- Calendar Link -->
        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
            <div>
                @if($upcomingClosingDays->isNotEmpty())
                <p class="text-xs text-gray-500">
                    Nächster Schließtag:
                    <span class="font-medium text-gray-700">{{ $upcomingClosingDays->first()->date->format('d.m.Y') }}</span>
                    @if($upcomingClosingDays->first()->label)
                    – {{ $upcomingClosingDays->first()->label }}
                    @endif
                </p>
                @else
                <p class="text-xs text-gray-400">Keine bevorstehenden Schließtage</p>
                @endif
            </div>
            <a href="{{ route('kitas.calendar', $kita) }}"
               class="inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Schließtage-Kalender
            </a>
        </div>
    </div>

    <!-- Employee List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-base font-semibold text-gray-800">Mitarbeiter ({{ $employees->count() }} aktiv)</h3>
            @if(session('user_role') !== 'KITA_STAFF')
            <a href="/employees/create" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Neu
            </a>
            @endif
        </div>
        @if($employees->isEmpty())
        <div class="p-8 text-center text-gray-400">
            <p>Noch keine aktiven Mitarbeiter eingetragen.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vertragsart</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Wochenstunden</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($employees as $employee)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="/employees/{{ $employee->id }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                {{ $employee->full_name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $employee->position ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $employee->contract_type_label }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ number_format($employee->weekly_hours, 1, ',', '.') }} h</td>
                        <td class="px-6 py-4">
                            <a href="/employees/{{ $employee->id }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
