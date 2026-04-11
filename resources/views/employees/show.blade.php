@extends('layouts.app')

@section('title', $employee->full_name)
@section('page-title', $employee->full_name)

@section('content')
<div x-data="{ activeTab: 'stammdaten' }" class="space-y-4">

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-start space-x-4">
                <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-700 font-bold text-xl">{{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $employee->full_name }}</h2>
                    <p class="text-gray-500 text-sm">{{ $employee->position ?? 'Keine Funktion angegeben' }}</p>
                    <div class="flex items-center space-x-2 mt-2">
                        <span class="{{ $employee->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }} px-2.5 py-0.5 rounded-full text-xs font-medium">
                            {{ $employee->is_active ? 'Aktiv' : 'Inaktiv' }}
                        </span>
                        <span class="bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full text-xs font-medium">
                            {{ $employee->contract_type_label }}
                        </span>
                        <span class="text-xs text-gray-500">{{ $employee->kita->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
            @if(session('user_role') !== 'KITA_STAFF')
            <div class="flex space-x-2 flex-shrink-0">
                <a href="/employees/{{ $employee->id }}/edit"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Bearbeiten
                </a>
                <form action="/employees/{{ $employee->id }}" method="POST"
                      onsubmit="return confirm('Mitarbeiter wirklich löschen? Alle Dokumente und Schulungseinträge werden ebenfalls gelöscht.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Löschen
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 bg-white rounded-t-xl shadow-sm">
        <nav class="flex -mb-px px-6 space-x-6 overflow-x-auto">
            <button @click="activeTab = 'stammdaten'"
                    :class="activeTab === 'stammdaten' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">
                Stammdaten
            </button>
            <button @click="activeTab = 'dokumente'"
                    :class="activeTab === 'dokumente' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">
                Dokumente ({{ $employee->documents->count() }})
            </button>
            <button @click="activeTab = 'schulungen'"
                    :class="activeTab === 'schulungen' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors">
                Schulungen ({{ $employee->trainingCompletions->count() }})
            </button>
        </nav>
    </div>

    <!-- Tab Contents -->

    <!-- Stammdaten -->
    <div x-show="activeTab === 'stammdaten'" class="bg-white rounded-b-xl shadow-sm p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Kontakt</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500">E-Mail</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $employee->email ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Telefon</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $employee->phone ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Adresse</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $employee->address ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Geburtsdatum</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $employee->birth_date ? $employee->birth_date->format('d.m.Y') : '-' }}</dd>
                    </div>
                </dl>
            </div>
            <div>
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Beschäftigung</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500">Eintrittsdatum</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $employee->start_date->format('d.m.Y') }}</dd>
                    </div>
                    @if($employee->end_date)
                    <div>
                        <dt class="text-xs text-gray-500">Austrittsdatum</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $employee->end_date->format('d.m.Y') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-500">Vertragsart</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $employee->contract_type_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Wochenstunden</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ number_format($employee->weekly_hours, 1, ',', '.') }} Std.</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Kita</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">
                            <a href="/kitas/{{ $employee->kita_id }}" class="text-indigo-600 hover:text-indigo-800">{{ $employee->kita->name ?? '-' }}</a>
                        </dd>
                    </div>
                </dl>
            </div>
            @if($employee->notes)
            <div class="sm:col-span-2">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Notizen</h3>
                <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $employee->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Dokumente -->
    <div x-show="activeTab === 'dokumente'" x-cloak class="bg-white rounded-b-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-base font-semibold text-gray-800">Dokumente</h3>
            @if(session('user_role') !== 'KITA_STAFF')
            <a href="/employees/{{ $employee->id }}/documents"
               class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                Dokument hochladen
            </a>
            @endif
        </div>
        @if($employee->documents->isEmpty())
        <div class="p-8 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>Noch keine Dokumente hochgeladen.</p>
            @if(session('user_role') !== 'KITA_STAFF')
            <a href="/employees/{{ $employee->id }}/documents" class="text-indigo-600 hover:text-indigo-800 text-sm mt-2 inline-block">Jetzt hochladen</a>
            @endif
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($employee->documents as $doc)
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        @if(str_contains($doc->mime_type, 'pdf'))
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                        @elseif(str_contains($doc->mime_type, 'image'))
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                        @else
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $doc->label ?: $doc->file_name }}</p>
                        <p class="text-xs text-gray-400">{{ $doc->file_name }} &middot; {{ $doc->formatted_size }} &middot; {{ $doc->uploaded_at->format('d.m.Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="/employees/{{ $employee->id }}/documents/{{ $doc->id }}/download"
                       class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Herunterladen</a>
                    @if(session('user_role') !== 'KITA_STAFF')
                    <span class="text-gray-300">|</span>
                    <form action="/employees/{{ $employee->id }}/documents/{{ $doc->id }}" method="POST"
                          onsubmit="return confirm('Dokument wirklich löschen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Löschen</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Schulungen -->
    <div x-show="activeTab === 'schulungen'" x-cloak class="bg-white rounded-b-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-base font-semibold text-gray-800">Schulungen</h3>
            @if(session('user_role') !== 'KITA_STAFF')
            <a href="/employees/{{ $employee->id }}/training/create"
               class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                Schulung eintragen
            </a>
            @endif
        </div>
        @if($employee->trainingCompletions->isEmpty())
        <div class="p-8 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p>Noch keine Schulungen eingetragen.</p>
            @if(session('user_role') !== 'KITA_STAFF')
            <a href="/employees/{{ $employee->id }}/training/create" class="text-indigo-600 hover:text-indigo-800 text-sm mt-2 inline-block">Jetzt eintragen</a>
            @endif
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Schulung</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Abgeschlossen</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Gültig bis</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($employee->trainingCompletions->sortByDesc('completed_date') as $completion)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-800">{{ $completion->category->name }}</span>
                            @if($completion->category->is_first_aid)
                            <span class="ml-2 px-1.5 py-0.5 text-xs bg-red-100 text-red-700 rounded">Erste Hilfe</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $completion->completed_date->format('d.m.Y') }}</td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $completion->expiry_date ? $completion->expiry_date->format('d.m.Y') : 'Unbegrenzt' }}
                        </td>
                        <td class="px-6 py-4">
                            @php $status = $completion->expiryStatus(); @endphp
                            @if($status === 'valid')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Gültig</span>
                            @elseif($status === 'expiring')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Läuft bald ab</span>
                            @elseif($status === 'expired')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Abgelaufen</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Kein Ablaufdatum</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                @if(session('user_role') !== 'KITA_STAFF')
                                <a href="/training/completions/{{ $completion->id }}/edit"
                                   class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Bearbeiten</a>
                                <span class="text-gray-300">|</span>
                                <form action="/training/completions/{{ $completion->id }}" method="POST"
                                      onsubmit="return confirm('Schulungseintrag wirklich löschen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Löschen</button>
                                </form>
                                @endif
                                @if($completion->notes)
                                <span class="text-gray-400 text-xs" title="{{ $completion->notes }}">Notiz</span>
                                @endif
                            </div>
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
