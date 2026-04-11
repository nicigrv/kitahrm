@extends('layouts.app')

@section('title', 'Schließtage – ' . $kita->name)
@section('page-title', 'Schließtage: ' . $kita->name)

@section('content')
@php
    $monthNames = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
    $dayNames   = ['Mo','Di','Mi','Do','Fr','Sa','So'];
    $firstDay   = \Carbon\Carbon::create($year, $month, 1);
    $daysInMonth = $firstDay->daysInMonth;
    // Monday = 0, shift weekday: Carbon Mon=1 → 0-indexed = dayOfWeek-1, Sun=0→6
    $startOffset = ($firstDay->dayOfWeek + 6) % 7; // 0=Mon … 6=Sun
    $today       = now()->format('Y-m-d');
    $prevUrl     = route('kitas.calendar', ['kita' => $kita->id, 'year' => $month===1 ? $year-1 : $year, 'month' => $month===1 ? 12 : $month-1]);
    $nextUrl     = route('kitas.calendar', ['kita' => $kita->id, 'year' => $month===12 ? $year+1 : $year, 'month' => $month===12 ? 1 : $month+1]);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- Calendar Panel -->
    <div class="lg:col-span-2 space-y-4">

        <!-- Add closing day form -->
        @if(session('user_role') !== 'KITA_STAFF')
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Schließtag eintragen</h3>
            <form method="POST" action="{{ route('kitas.closing-days.store', $kita) }}" class="flex flex-wrap gap-3">
                @csrf
                <div class="flex-1 min-w-[140px]">
                    <input type="date" name="date" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex-1 min-w-[160px]">
                    <input type="text" name="label" placeholder="Bezeichnung (z.B. Betriebsferien)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Eintragen
                </button>
            </form>
            @if(session('success'))
            <p class="mt-2 text-sm text-green-600">{{ session('success') }}</p>
            @endif
        </div>
        @endif

        <!-- Month navigation -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <a href="{{ $prevUrl }}" class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h2 class="text-base font-semibold text-gray-800">
                    {{ $monthNames[$month - 1] }} {{ $year }}
                </h2>
                <a href="{{ $nextUrl }}" class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <!-- Day headers -->
            <div class="grid grid-cols-7 border-b border-gray-100">
                @foreach($dayNames as $dn)
                <div class="py-2 text-center text-xs font-semibold text-gray-400">{{ $dn }}</div>
                @endforeach
            </div>

            <!-- Calendar grid -->
            <div class="grid grid-cols-7">
                {{-- empty cells before first day --}}
                @for($i = 0; $i < $startOffset; $i++)
                <div class="min-h-[72px] border-b border-r border-gray-50 bg-gray-50/50"></div>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateStr  = sprintf('%d-%02d-%02d', $year, $month, $day);
                    $closing  = $closingDays->get($dateStr);
                    $isToday  = $dateStr === $today;
                    $dayOfWeek = (new \Carbon\Carbon($dateStr))->dayOfWeek; // 0=Sun,6=Sat
                    $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;
                    $col = (($startOffset + $day - 1) % 7);
                    $isLastCol = $col === 6;
                @endphp
                <div class="min-h-[72px] border-b {{ $isLastCol ? '' : 'border-r' }} border-gray-100 p-1.5 relative
                    {{ $closing ? 'bg-red-50' : ($isWeekend ? 'bg-gray-50/60' : 'bg-white') }}">

                    <!-- Day number -->
                    <span class="inline-flex w-6 h-6 items-center justify-center text-xs font-medium rounded-full
                        {{ $isToday ? 'bg-indigo-600 text-white' : ($isWeekend ? 'text-gray-400' : 'text-gray-700') }}">
                        {{ $day }}
                    </span>

                    @if($closing)
                    <!-- Closing day badge -->
                    <div class="mt-1 text-xs leading-tight text-red-700 font-medium break-words">
                        {{ $closing->label ?: 'Schließtag' }}
                    </div>
                    @if(session('user_role') !== 'KITA_STAFF')
                    <form method="POST"
                          action="{{ route('kitas.closing-days.destroy', [$kita, $closing]) }}"
                          class="absolute top-1 right-1"
                          onsubmit="return confirm('Schließtag {{ $dateStr }} entfernen?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                    @endif
                </div>
                @endfor

                {{-- fill remaining cells --}}
                @php $total = $startOffset + $daysInMonth; $remainder = (7 - ($total % 7)) % 7; @endphp
                @for($i = 0; $i < $remainder; $i++)
                <div class="min-h-[72px] border-b border-r border-gray-50 bg-gray-50/50"></div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Sidebar: upcoming + legend -->
    <div class="space-y-4">

        <!-- Navigate to current month -->
        <a href="{{ route('kitas.calendar', $kita) }}"
           class="block text-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
            Aktueller Monat
        </a>

        <!-- Upcoming closing days -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Bevorstehende Schließtage</h3>
            @if($upcomingDays->isEmpty())
            <p class="text-sm text-gray-400">Keine Schließtage eingetragen.</p>
            @else
            <ul class="space-y-2">
                @foreach($upcomingDays as $ud)
                <li class="flex items-start justify-between gap-2">
                    <div>
                        <div class="text-sm font-medium text-gray-800">{{ $ud->date->format('d.m.Y') }}</div>
                        @if($ud->label)
                        <div class="text-xs text-gray-400">{{ $ud->label }}</div>
                        @endif
                    </div>
                    @if(session('user_role') !== 'KITA_STAFF')
                    <form method="POST" action="{{ route('kitas.closing-days.destroy', [$kita, $ud]) }}"
                          onsubmit="return confirm('Entfernen?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 text-xs transition-colors mt-0.5">×</button>
                    </form>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        <!-- Legend -->
        <div class="bg-white rounded-xl shadow-sm p-5 text-xs text-gray-500 space-y-2">
            <p class="font-semibold text-gray-700 text-sm mb-2">Legende</p>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-red-50 border border-red-200 flex-shrink-0"></div>
                <span>Eingetragener Schließtag</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-50 border border-gray-200 flex-shrink-0"></div>
                <span>Wochenende</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-full bg-indigo-600 flex-shrink-0"></div>
                <span>Heutiger Tag</span>
            </div>
        </div>

        <a href="{{ route('kitas.show', $kita) }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-800">
            ← Zurück zur Einrichtung
        </a>
    </div>
</div>
@endsection
