@extends('layouts.app')

@section('title', 'Kitas')
@section('page-title', 'Kita-Übersicht')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <p class="text-sm text-gray-500">{{ count($kitaData) }} Einrichtung(en)</p>
        @if(session('user_role') === 'ADMIN')
        <a href="/kitas/create"
           class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex-shrink-0">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Neue Kita anlegen
        </a>
        @endif
    </div>

    <!-- Kita Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($kitaData as $item)
        @php $kita = $item['kita']; @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
            <!-- Card Header -->
            <div class="px-5 py-4 border-b border-gray-50 flex items-start justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-indigo-700 font-bold text-sm">{{ $kita->short_code }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">
                            <a href="/kitas/{{ $kita->id }}" class="hover:text-indigo-600 transition-colors">{{ $kita->name }}</a>
                        </h3>
                        @if($kita->address)
                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[160px]">{{ $kita->address }}</p>
                        @endif
                    </div>
                </div>
                @if(session('user_role') === 'ADMIN')
                <div class="flex space-x-1">
                    <a href="/kitas/{{ $kita->id }}/edit"
                       class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors"
                       title="Bearbeiten">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <form method="POST" action="/kitas/{{ $kita->id }}" onsubmit="return confirm('Kita wirklich löschen?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Löschen">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Stats -->
            <div class="px-5 py-4 grid grid-cols-2 gap-3">
                <!-- Weekly Hours -->
                <div>
                    <div class="flex items-end gap-1">
                        <span class="text-2xl font-bold {{ $item['hours_ok'] ? 'text-gray-800' : 'text-red-600' }}">
                            {{ number_format($item['actual_hours'], 1, ',', '.') }}
                        </span>
                        @if($item['target_hours'] > 0)
                        <span class="text-sm text-gray-400 mb-0.5">/{{ number_format($item['target_hours'], 1, ',', '.') }} h</span>
                        @else
                        <span class="text-sm text-gray-400 mb-0.5">h/Wo</span>
                        @endif
                    </div>
                    @if($item['target_hours'] > 0)
                    <div class="mt-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        @php $pct = min(100, $item['target_hours'] > 0 ? ($item['actual_hours'] / $item['target_hours'] * 100) : 0); @endphp
                        <div class="h-full rounded-full {{ $item['hours_ok'] ? 'bg-green-500' : 'bg-red-400' }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    @endif
                    <div class="text-xs text-gray-500 mt-1">Stunden / Woche</div>
                </div>

                <!-- First Aid + Status -->
                <div>
                    <div class="flex items-end gap-1">
                        <span class="text-2xl font-bold {{ $item['first_aid_ok'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $item['first_aid_count'] }}
                        </span>
                        @if($kita->min_first_aid > 0)
                        <span class="text-sm text-gray-400 mb-0.5">/{{ $kita->min_first_aid }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Ersthelfer</div>
                    <div class="mt-2 flex flex-wrap gap-1">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium {{ $item['first_aid_ok'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $item['first_aid_ok'] ? 'EH ✓' : 'EH ✗' }}
                        </span>
                        @if($item['target_hours'] > 0)
                        <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium {{ $item['hours_ok'] ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $item['hours_ok'] ? 'Besetzt' : 'Unterbes.' }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-3 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3 text-xs text-gray-400">
                    @if($kita->phone)
                    <span class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $kita->phone }}
                    </span>
                    @endif
                </div>
                <a href="/kitas/{{ $kita->id }}"
                   class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                    Details →
                </a>
            </div>
        </div>
        @endforeach
    </div>

    @if(count($kitaData) === 0)
    <div class="bg-white rounded-xl shadow-sm border border-dashed border-gray-200 p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <p class="text-gray-500 font-medium">Noch keine Kitas vorhanden</p>
        @if(session('user_role') === 'ADMIN')
        <a href="/kitas/create" class="mt-3 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
            Erste Kita anlegen →
        </a>
        @endif
    </div>
    @endif

</div>
@endsection
