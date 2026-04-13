@extends('layouts.app')

@section('title', 'Gemeinsamer Kalender')
@section('page-title', 'Gemeinsamer Kalender')

@section('content')
@php
    $monthNames = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
    $dayNames   = ['Mo','Di','Mi','Do','Fr','Sa','So'];
    $firstDay   = \Carbon\Carbon::create($year, $month, 1);
    $daysInMonth = $firstDay->daysInMonth;
    $startOffset = ($firstDay->dayOfWeek + 6) % 7; // 0=Mon … 6=Sun
    $today   = now()->format('Y-m-d');
    $prevUrl = route('calendar.index', ['year' => $month===1 ? $year-1 : $year, 'month' => $month===1 ? 12 : $month-1]);
    $nextUrl = route('calendar.index', ['year' => $month===12 ? $year+1 : $year, 'month' => $month===12 ? 1 : $month+1]);

    // Assign a Tailwind color name to each kita by insertion order
    $palette     = ['indigo','rose','emerald','amber','violet','cyan','orange','pink','lime','teal'];
    $kitaColorMap = [];
    foreach ($allKitas as $i => $k) {
        $kitaColorMap[$k->id] = $palette[$i % count($palette)];
    }

    // Predefined Tailwind class sets per colour (CDN builds include everything)
    $colorClasses = [
        'indigo'  => ['bg'=>'bg-indigo-100',  'text'=>'text-indigo-800',  'border'=>'border-indigo-400',  'dot'=>'bg-indigo-500'],
        'rose'    => ['bg'=>'bg-rose-100',    'text'=>'text-rose-800',    'border'=>'border-rose-400',    'dot'=>'bg-rose-500'],
        'emerald' => ['bg'=>'bg-emerald-100', 'text'=>'text-emerald-800', 'border'=>'border-emerald-400', 'dot'=>'bg-emerald-500'],
        'amber'   => ['bg'=>'bg-amber-100',   'text'=>'text-amber-800',   'border'=>'border-amber-400',   'dot'=>'bg-amber-500'],
        'violet'  => ['bg'=>'bg-violet-100',  'text'=>'text-violet-800',  'border'=>'border-violet-400',  'dot'=>'bg-violet-500'],
        'cyan'    => ['bg'=>'bg-cyan-100',    'text'=>'text-cyan-800',    'border'=>'border-cyan-400',    'dot'=>'bg-cyan-500'],
        'orange'  => ['bg'=>'bg-orange-100',  'text'=>'text-orange-800',  'border'=>'border-orange-400',  'dot'=>'bg-orange-500'],
        'pink'    => ['bg'=>'bg-pink-100',    'text'=>'text-pink-800',    'border'=>'border-pink-400',    'dot'=>'bg-pink-500'],
        'lime'    => ['bg'=>'bg-lime-100',    'text'=>'text-lime-800',    'border'=>'border-lime-400',    'dot'=>'bg-lime-500'],
        'teal'    => ['bg'=>'bg-teal-100',    'text'=>'text-teal-800',    'border'=>'border-teal-400',    'dot'=>'bg-teal-500'],
        'gray'    => ['bg'=>'bg-gray-100',    'text'=>'text-gray-700',    'border'=>'border-gray-400',    'dot'=>'bg-gray-400'],
    ];

    $typeIcons = [
        'SCHLIESSTAG'   => '🔒',
        'KURZE_ZEITEN'  => '⏰',
        'FORTBILDUNG'   => '📚',
        'INFO'          => 'ℹ️',
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Calendar panel ─────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        <div class="bg-white rounded-xl shadow-sm" style="overflow:visible">

            {{-- Month navigation --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <a href="{{ $prevUrl }}" class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h2 class="text-base font-semibold text-gray-800">{{ $monthNames[$month-1] }} {{ $year }}</h2>
                <a href="{{ $nextUrl }}" class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            {{-- Day headers --}}
            <div class="grid grid-cols-7 border-b border-gray-100 bg-gray-50">
                @foreach($dayNames as $dn)
                <div class="py-2 text-center text-xs font-semibold text-gray-400">{{ $dn }}</div>
                @endforeach
            </div>

            {{-- Calendar grid --}}
            <div class="grid grid-cols-7">

                {{-- Leading empty cells --}}
                @for($i = 0; $i < $startOffset; $i++)
                <div class="min-h-[88px] border-b border-r border-gray-50 bg-gray-50/30"></div>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateStr   = sprintf('%d-%02d-%02d', $year, $month, $day);
                    $dayEvents = $eventsByDate[$dateStr] ?? [];
                    $isToday   = $dateStr === $today;
                    $dow       = (\Carbon\Carbon::create($year, $month, $day))->dayOfWeek;
                    $isWeekend = $dow === 0 || $dow === 6;
                    $col       = ($startOffset + $day - 1) % 7;
                    $isLastCol = $col === 6;
                    $hasClosed = collect($dayEvents)->contains('event_type', 'SCHLIESSTAG');
                @endphp
                <div class="min-h-[88px] border-b {{ $isLastCol ? '' : 'border-r' }} border-gray-100 p-1 relative
                    {{ $hasClosed ? 'bg-red-50/40' : ($isWeekend ? 'bg-gray-50/50' : 'bg-white') }}">

                    {{-- Day number --}}
                    <span class="inline-flex w-6 h-6 items-center justify-center text-xs font-medium rounded-full mb-0.5
                        {{ $isToday ? 'bg-indigo-600 text-white' : ($isWeekend ? 'text-gray-400' : 'text-gray-700') }}">
                        {{ $day }}
                    </span>

                    {{-- Events (max 2 shown, rest collapsed) --}}
                    @foreach(array_slice($dayEvents, 0, 2) as $ev)
                    @php
                        $cName = $kitaColorMap[$ev->kita_id] ?? 'gray';
                        $cc    = $colorClasses[$cName];
                    @endphp
                    <div class="group relative mt-0.5 px-1.5 py-0.5 rounded text-xs leading-tight truncate {{ $cc['bg'] }} {{ $cc['text'] }} border-l-2 {{ $cc['border'] }} cursor-default">
                        <span class="mr-0.5">{{ $typeIcons[$ev->event_type] ?? '' }}</span>{{ $ev->title }}
                        {{-- Hover tooltip --}}
                        <div class="absolute bottom-full left-0 mb-1 z-50 pointer-events-none
                                    invisible group-hover:visible opacity-0 group-hover:opacity-100
                                    transition-opacity duration-150
                                    bg-gray-900 text-white rounded-lg shadow-xl px-2.5 py-2
                                    min-w-[160px] max-w-[220px] whitespace-normal">
                            <div class="flex items-center gap-1.5 font-semibold text-xs mb-0.5">
                                <span class="w-2 h-2 rounded-full {{ $cc['dot'] }} flex-shrink-0"></span>
                                <span>{{ $ev->kita->name ?? '–' }}</span>
                            </div>
                            <div class="text-gray-300 text-xs">{{ $typeIcons[$ev->event_type] ?? '' }} {{ $ev->type_label }}</div>
                            @if($ev->start_time)
                            <div class="text-gray-400 text-xs">{{ $ev->start_time }}{{ $ev->end_time ? ' – ' . $ev->end_time : '' }}</div>
                            @endif
                            @if($ev->end_date && $ev->end_date->ne($ev->date))
                            <div class="text-gray-400 text-xs">{{ $ev->date->format('d.m.') }} – {{ $ev->end_date->format('d.m.Y') }}</div>
                            @endif
                            @if($ev->description)
                            <div class="text-gray-400 text-xs mt-0.5 leading-snug">{{ \Str::limit($ev->description, 80) }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    @if(count($dayEvents) > 2)
                    <div class="mt-0.5 px-1 text-xs text-gray-400">+{{ count($dayEvents) - 2 }} weitere</div>
                    @endif
                </div>
                @endfor

                {{-- Trailing empty cells --}}
                @php $total = $startOffset + $daysInMonth; $remainder = (7 - ($total % 7)) % 7; @endphp
                @for($i = 0; $i < $remainder; $i++)
                <div class="min-h-[88px] border-b border-r border-gray-50 bg-gray-50/30"></div>
                @endfor
            </div>
        </div>
    </div>

    {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Current month button --}}
        <a href="{{ route('calendar.index') }}"
           class="block text-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
            Aktueller Monat
        </a>

        {{-- Add event form (ADMIN / KITA_MANAGER) --}}
        @if(session('user_role') !== 'KITA_STAFF')
        <div x-data="{ type: 'SCHLIESSTAG', showEnd: false }" class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Eintrag hinzufügen</h3>

            @if(session('success'))
            <p class="mb-3 text-sm text-green-600">{{ session('success') }}</p>
            @endif

            <form method="POST" action="{{ route('calendar.store') }}" class="space-y-2.5">
                @csrf

                {{-- Kita selector (admins only) --}}
                @if(session('user_role') === 'ADMIN')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Einrichtung <span class="text-red-500">*</span></label>
                    <select name="kita_id" required
                            class="w-full px-3 py-2 border {{ $errors->has('kita_id') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Bitte wählen …</option>
                        @foreach($allKitas as $k)
                        <option value="{{ $k->id }}" {{ old('kita_id') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                        @endforeach
                    </select>
                    @error('kita_id')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                @endif

                {{-- Event type --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Typ <span class="text-red-500">*</span></label>
                    <select name="event_type" x-model="type" required
                            class="w-full px-3 py-2 border {{ $errors->has('event_type') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($eventTypes as $val => $label)
                        <option value="{{ $val }}" {{ old('event_type', 'SCHLIESSTAG') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('event_type')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bezeichnung <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="z.B. Betriebsferien, Ausfall …"
                           class="w-full px-3 py-2 border {{ $errors->has('title') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('title')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Date --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Datum <span class="text-red-500">*</span></label>
                        <input type="date" name="date" value="{{ old('date') }}" required
                               class="w-full px-3 py-2 border {{ $errors->has('date') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('date')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div x-show="showEnd" x-cloak>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bis</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}"
                               class="w-full px-3 py-2 border {{ $errors->has('end_date') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('end_date')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Multi-day toggle --}}
                <button type="button" @click="showEnd=!showEnd"
                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    <span x-text="showEnd ? '– Enddatum entfernen' : '+ Mehrtägig (Enddatum)'"></span>
                </button>

                {{-- Time fields (only for KURZE_ZEITEN) --}}
                <div x-show="type==='KURZE_ZEITEN'" x-cloak class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Von</label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bis</label>
                        <input type="time" name="end_time" value="{{ old('end_time') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Beschreibung <span class="text-gray-400">(optional)</span></label>
                    <textarea name="description" rows="2"
                              class="w-full px-3 py-2 border {{ $errors->has('description') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Speichern
                </button>
            </form>
        </div>
        @endif

        {{-- Kita legend (colors) --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Einrichtungen</h3>
            <div class="space-y-2">
                @foreach($allKitas as $k)
                @php $cc = $colorClasses[$kitaColorMap[$k->id] ?? 'gray']; @endphp
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full {{ $cc['dot'] }} flex-shrink-0"></span>
                    <span class="text-sm text-gray-700">{{ $k->name }}</span>
                    <span class="text-xs text-gray-400 font-mono ml-auto">{{ $k->short_code }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Event type legend --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Ereignistypen</h3>
            <div class="space-y-1.5 text-sm text-gray-600">
                @foreach($eventTypes as $type => $label)
                <div class="flex items-center gap-2">
                    <span class="text-base leading-none">{{ $typeIcons[$type] ?? '' }}</span>
                    <span>{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Upcoming events --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Bevorstehende Ereignisse</h3>
            @if($upcomingEvents->isEmpty())
            <p class="text-sm text-gray-400">Keine bevorstehenden Einträge.</p>
            @else
            <ul class="space-y-2.5">
                @foreach($upcomingEvents as $ev)
                @php $cc = $colorClasses[$kitaColorMap[$ev->kita_id] ?? 'gray']; @endphp
                <li class="flex items-start gap-2">
                    <span class="mt-1.5 w-2.5 h-2.5 rounded-full {{ $cc['dot'] }} flex-shrink-0"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">
                            {{ $typeIcons[$ev->event_type] ?? '' }} {{ $ev->title }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $ev->date->format('d.m.Y') }}
                            @if($ev->end_date && $ev->end_date->ne($ev->date)) – {{ $ev->end_date->format('d.m.Y') }} @endif
                            @if($ev->start_time) · {{ $ev->start_time }}{{ $ev->end_time ? '–' . $ev->end_time : '' }} @endif
                            · {{ $ev->kita->name ?? '–' }}
                        </p>
                        @if($ev->description)
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $ev->description }}</p>
                        @endif
                    </div>
                    @if(session('user_role') !== 'KITA_STAFF' && (session('user_role') === 'ADMIN' || (int)$ev->kita_id === (int)session('user_kita_id')))
                    <form method="POST" action="{{ route('calendar.destroy', $ev) }}"
                          onsubmit="return confirm('Eintrag löschen?')" class="flex-shrink-0">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 text-lg leading-none transition-colors">×</button>
                    </form>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>

    </div>
</div>
@endsection
