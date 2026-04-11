@extends('layouts.app')

@section('title', 'Schulungsmatrix')
@section('page-title', 'Schulungsmatrix')

@section('content')
<div class="space-y-4">

    <!-- Kita Selector (Admin) -->
    @if(session('user_role') === 'ADMIN')
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="/training" class="flex items-center space-x-3">
            <label class="text-sm font-medium text-gray-700">Kita wählen:</label>
            <select name="kita_id" onchange="this.form.submit()"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="">Bitte wählen...</option>
                @foreach($kitas as $kita)
                <option value="{{ $kita->id }}" {{ $selectedKitaId == $kita->id ? 'selected' : '' }}>{{ $kita->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    @endif

    @if($selectedKita)
    <!-- Legend -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap gap-4 items-center">
            <span class="text-sm font-medium text-gray-700">Legende:</span>
            <div class="flex items-center space-x-2">
                <div class="w-5 h-5 rounded-full bg-green-500 border-2 border-green-600"></div>
                <span class="text-xs text-gray-600">Gültig</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-5 h-5 rounded-full bg-yellow-400 border-2 border-yellow-500"></div>
                <span class="text-xs text-gray-600">Läuft ab (&#x2264;60 Tage)</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-5 h-5 rounded-full bg-red-500 border-2 border-red-600"></div>
                <span class="text-xs text-gray-600">Abgelaufen</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-5 h-5 rounded-full bg-gray-200 border-2 border-gray-300"></div>
                <span class="text-xs text-gray-600">Kein Ablaufdatum</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-5 h-5 rounded-full border-2 border-dashed border-gray-300"></div>
                <span class="text-xs text-gray-600">Nicht absolviert</span>
            </div>
        </div>
    </div>

    <!-- Matrix Table -->
    @if($employees->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
        <p>Keine aktiven Mitarbeiter in dieser Kita.</p>
    </div>
    @elseif($categories->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
        <p>Keine aktiven Schulungskategorien vorhanden.</p>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="text-sm border-collapse w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 border-b border-r border-gray-200 sticky left-0 bg-gray-50 min-w-[180px]">
                            Mitarbeiter
                        </th>
                        @foreach($categories as $category)
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 border-b border-r border-gray-200 min-w-[120px]">
                            <div class="flex flex-col items-center space-y-1">
                                <span>{{ $category->name }}</span>
                                @if($category->is_first_aid)
                                <span class="px-1.5 py-0.5 text-xs bg-red-100 text-red-700 rounded">EH</span>
                                @endif
                                @if($category->validity_months)
                                <span class="text-gray-400 text-xs font-normal">{{ $category->validity_months }} Mon.</span>
                                @endif
                            </div>
                        </th>
                        @endforeach
                        @if(session('user_role') !== 'KITA_STAFF')
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 border-b border-gray-200 min-w-[80px]">Aktionen</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($employees as $employee)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 border-r border-gray-200 sticky left-0 bg-white">
                            <a href="/employees/{{ $employee->id }}" class="font-medium text-indigo-600 hover:text-indigo-800 text-sm">
                                {{ $employee->full_name }}
                            </a>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $employee->position ?? '' }}</p>
                        </td>
                        @foreach($categories as $category)
                        @php $completion = $matrix[$employee->id][$category->id] ?? null; @endphp
                        <td class="px-3 py-3 text-center border-r border-gray-200">
                            @if($completion)
                            @php $status = $completion->expiryStatus(); @endphp
                            <div class="group relative inline-block">
                                @if($status === 'valid')
                                <div class="w-7 h-7 rounded-full bg-green-500 border-2 border-green-600 mx-auto cursor-pointer hover:scale-110 transition-transform" title="{{ $completion->completed_date->format('d.m.Y') }}{{ $completion->expiry_date ? ' – gültig bis ' . $completion->expiry_date->format('d.m.Y') : '' }}"></div>
                                @elseif($status === 'expiring')
                                <div class="w-7 h-7 rounded-full bg-yellow-400 border-2 border-yellow-500 mx-auto cursor-pointer hover:scale-110 transition-transform" title="Läuft ab: {{ $completion->expiry_date->format('d.m.Y') }}"></div>
                                @elseif($status === 'expired')
                                <div class="w-7 h-7 rounded-full bg-red-500 border-2 border-red-600 mx-auto cursor-pointer hover:scale-110 transition-transform" title="Abgelaufen: {{ $completion->expiry_date->format('d.m.Y') }}"></div>
                                @else
                                <div class="w-7 h-7 rounded-full bg-gray-200 border-2 border-gray-300 mx-auto cursor-pointer hover:scale-110 transition-transform" title="{{ $completion->completed_date->format('d.m.Y') }} – kein Ablaufdatum"></div>
                                @endif

                                @if(session('user_role') !== 'KITA_STAFF')
                                <!-- Tooltip/Popup on hover -->
                                <div class="hidden group-hover:block absolute z-20 bottom-full left-1/2 -translate-x-1/2 mb-2 w-52 bg-gray-900 text-white text-xs rounded-lg p-2 shadow-lg">
                                    <p class="font-medium">{{ $category->name }}</p>
                                    <p class="mt-1">Absolviert: {{ $completion->completed_date->format('d.m.Y') }}</p>
                                    @if($completion->expiry_date)
                                    <p>Gültig bis: {{ $completion->expiry_date->format('d.m.Y') }}</p>
                                    @endif
                                    <div class="flex space-x-2 mt-2 pt-2 border-t border-gray-700">
                                        <a href="/training/completions/{{ $completion->id }}/edit" class="text-indigo-300 hover:text-indigo-100">Bearbeiten</a>
                                    </div>
                                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-gray-900"></div>
                                </div>
                                @endif
                            </div>
                            @else
                            @if(session('user_role') !== 'KITA_STAFF')
                            <a href="/employees/{{ $employee->id }}/training/create?category_id={{ $category->id }}"
                               class="w-7 h-7 rounded-full border-2 border-dashed border-gray-300 hover:border-indigo-400 hover:bg-indigo-50 mx-auto flex items-center justify-center transition-colors group"
                               title="Schulung eintragen">
                                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </a>
                            @else
                            <div class="w-7 h-7 rounded-full border-2 border-dashed border-gray-200 mx-auto"></div>
                            @endif
                            @endif
                        </td>
                        @endforeach
                        @if(session('user_role') !== 'KITA_STAFF')
                        <td class="px-3 py-3 text-center">
                            <a href="/employees/{{ $employee->id }}/training/create"
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium whitespace-nowrap">+ Schulung</a>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @else
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
        @if(session('user_role') === 'ADMIN')
        <p>Bitte wählen Sie eine Kita aus, um die Schulungsmatrix anzuzeigen.</p>
        @else
        <p>Keine Kita zugewiesen.</p>
        @endif
    </div>
    @endif

</div>
@endsection
