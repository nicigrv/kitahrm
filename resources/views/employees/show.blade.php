@extends('layouts.app')

@section('title', $employee->full_name)
@section('page-title', $employee->full_name)

@section('content')
@php
    $initTab = request('tab', 'stammdaten');
    $docCount = $employee->documents->count();
    $trainingCount = $employee->trainingCompletions->count();
    $tenure = $employee->start_date->diff(now());
    $tenureStr = $tenure->y > 0 ? $tenure->y . ' J. ' . $tenure->m . ' M.' : $tenure->m . ' Monate';
@endphp
<div x-data="{ tab: '{{ $initTab }}' }" class="space-y-4">

    <!-- Hero Header -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Colour bar -->
        <div class="h-2 {{ $employee->is_active ? 'bg-gradient-to-r from-indigo-500 to-blue-400' : 'bg-gray-300' }}"></div>

        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <!-- Avatar -->
                <div class="w-16 h-16 rounded-2xl bg-indigo-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <span class="text-indigo-700 font-bold text-2xl">
                        {{ strtoupper(substr($employee->first_name,0,1) . substr($employee->last_name,0,1)) }}
                    </span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $employee->full_name }}</h2>
                    <p class="text-gray-500 text-sm mt-0.5">{{ $employee->position ?: 'Keine Funktion angegeben' }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="{{ $employee->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }} px-2.5 py-0.5 rounded-full text-xs font-semibold">
                            {{ $employee->is_active ? 'Aktiv' : 'Inaktiv' }}
                        </span>
                        @php
                            $ctColors = ['UNBEFRISTET'=>'bg-blue-100 text-blue-800','BEFRISTET'=>'bg-yellow-100 text-yellow-800','MINIJOB'=>'bg-sky-100 text-sky-800','AUSBILDUNG'=>'bg-purple-100 text-purple-800','PRAKTIKUM'=>'bg-orange-100 text-orange-800','ELTERNZEIT'=>'bg-pink-100 text-pink-800'];
                        @endphp
                        <span class="{{ $ctColors[$employee->contract_type] ?? 'bg-gray-100 text-gray-700' }} px-2.5 py-0.5 rounded-full text-xs font-semibold">
                            {{ $employee->contract_type_label }}
                        </span>
                        <a href="{{ route('kitas.show', $employee->kita_id) }}"
                           class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                            {{ $employee->kita->name ?? '–' }}
                        </a>
                    </div>
                </div>
            </div>

            @if(session('user_role') !== 'KITA_STAFF')
            <div class="flex gap-2 flex-shrink-0">
                <a href="{{ route('employees.edit', $employee) }}"
                   class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Bearbeiten
                </a>
            </div>
            @endif
        </div>

        <!-- Quick stats row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 border-t border-gray-100">
            <div class="px-5 py-3 text-center border-r border-gray-100">
                <div class="text-lg font-bold text-gray-900">{{ number_format($employee->weekly_hours, 1, ',', '.') }}<span class="text-sm font-normal text-gray-400"> h</span></div>
                <div class="text-xs text-gray-500">Wochenstunden</div>
            </div>
            <div class="px-5 py-3 text-center sm:border-r border-gray-100">
                <div class="text-lg font-bold text-gray-900">{{ $tenureStr }}</div>
                <div class="text-xs text-gray-500">Betriebszugehörigkeit</div>
            </div>
            <div class="px-5 py-3 text-center border-t sm:border-t-0 border-r border-gray-100">
                <div class="text-lg font-bold text-gray-900">{{ $docCount }}</div>
                <div class="text-xs text-gray-500">Dokumente</div>
            </div>
            <div class="px-5 py-3 text-center border-t sm:border-t-0">
                <div class="text-lg font-bold text-gray-900">{{ $trainingCount }}</div>
                <div class="text-xs text-gray-500">Schulungen</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="flex border-b border-gray-200 px-2 overflow-x-auto">
            @foreach([['stammdaten','Stammdaten',''],['dokumente','Dokumente','(' . $docCount . ')'],['schulungen','Schulungen','(' . $trainingCount . ')']] as [$key,$label,$badge])
            <button @click="tab = '{{ $key }}'"
                    :class="tab==='{{ $key }}' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex items-center gap-1.5 py-3.5 px-3 border-b-2 text-sm font-medium whitespace-nowrap transition-colors mr-1">
                {{ $label }}
                @if($badge)
                <span :class="tab==='{{ $key }}' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500'"
                      class="text-xs px-1.5 py-0.5 rounded-full font-medium">{{ $badge }}</span>
                @endif
            </button>
            @endforeach
        </div>

        <!-- ── Stammdaten ─────────────────────────────────────────────────── -->
        <div x-show="tab==='stammdaten'" class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                <!-- Kontakt -->
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Kontakt</h3>
                    <dl class="space-y-3.5">
                        @foreach([
                            ['E-Mail',       $employee->email ? '<a href="mailto:'.$employee->email.'" class="text-indigo-600 hover:underline">'.$employee->email.'</a>' : '–'],
                            ['Telefon',      $employee->phone ?: '–'],
                            ['Adresse',      nl2br(e($employee->address)) ?: '–'],
                            ['Geburtsdatum', $employee->birth_date ? $employee->birth_date->format('d.m.Y') : '–'],
                        ] as [$dt, $dd])
                        <div class="flex gap-3">
                            <dt class="w-28 text-xs text-gray-400 pt-0.5 flex-shrink-0">{{ $dt }}</dt>
                            <dd class="text-sm text-gray-800">{!! $dd !!}</dd>
                        </div>
                        @endforeach
                    </dl>
                </div>
                <!-- Beschäftigung -->
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Beschäftigung</h3>
                    <dl class="space-y-3.5">
                        @foreach([
                            ['Eintrittsdatum', $employee->start_date->format('d.m.Y')],
                            ['Austrittsdatum', $employee->end_date ? $employee->end_date->format('d.m.Y') : '–'],
                            ['Vertragsart',    $employee->contract_type_label],
                            ['Wochenstunden',  number_format($employee->weekly_hours, 1, ',', '.') . ' h'],
                            ['Einrichtung',    '<a href="/kitas/'.$employee->kita_id.'" class="text-indigo-600 hover:underline">'.e($employee->kita->name ?? '–').'</a>'],
                        ] as [$dt, $dd])
                        <div class="flex gap-3">
                            <dt class="w-28 text-xs text-gray-400 pt-0.5 flex-shrink-0">{{ $dt }}</dt>
                            <dd class="text-sm text-gray-800">{!! $dd !!}</dd>
                        </div>
                        @endforeach
                    </dl>
                </div>
                @if($employee->notes)
                <div class="sm:col-span-2">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Notizen</h3>
                    <div class="bg-amber-50 border border-amber-100 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $employee->notes }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- ── Dokumente ──────────────────────────────────────────────────── -->
        <div x-show="tab==='dokumente'" x-cloak>

            @if(session('user_role') !== 'KITA_STAFF')
            <!-- Upload zone -->
            <div class="px-6 pt-5 pb-4 border-b border-gray-100"
                 x-data="{ dragover: false }"
                 @dragover.prevent="dragover=true"
                 @dragleave.prevent="dragover=false"
                 @drop.prevent="dragover=false; $refs.fileInput.files=$event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))">

                <form method="POST"
                      action="{{ route('employees.documents.store', $employee) }}"
                      enctype="multipart/form-data"
                      x-ref="uploadForm"
                      x-data="{ fileName: '' }">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('employees.show', $employee) }}?tab=dokumente">

                    <div :class="dragover ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 bg-gray-50'"
                         class="border-2 border-dashed rounded-xl p-6 text-center transition-colors cursor-pointer"
                         @click="$refs.fileInput.click()">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-600" x-text="fileName || 'Datei hierher ziehen oder klicken'"></p>
                        <p class="text-xs text-gray-400 mt-1">PDF, DOCX, JPG, PNG – max. 10 MB</p>
                        <input type="file" name="document" x-ref="fileInput" class="hidden" accept=".pdf,.docx,.jpg,.jpeg,.png"
                               @change="fileName = $event.target.files[0]?.name || ''">
                    </div>
                    @error('document') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="mt-3 flex gap-3">
                        <input type="text" name="label" value="{{ old('label') }}"
                               placeholder="Bezeichnung, z.B. Arbeitsvertrag, Zeugnis …"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" x-show="fileName"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Hochladen
                        </button>
                    </div>
                </form>
            </div>
            @endif

            @if(session('success') && request('tab') === 'dokumente')
            <div class="mx-6 mt-4 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                {{ session('success') }}
            </div>
            @endif

            <!-- Document list -->
            @if($employee->documents->isEmpty())
            <div class="p-10 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm">Noch keine Dokumente hochgeladen.</p>
            </div>
            @else
            <ul class="divide-y divide-gray-50 px-2 py-2">
                @foreach($employee->documents->sortByDesc('uploaded_at') as $doc)
                @php
                    $isDoc  = str_contains($doc->mime_type, 'pdf') || str_contains($doc->file_name, '.pdf');
                    $isWord = str_contains($doc->mime_type, 'word') || str_contains($doc->file_name, '.doc');
                    $isImg  = str_contains($doc->mime_type, 'image');
                    $icon   = $isDoc ? ['bg-red-50','text-red-500'] : ($isWord ? ['bg-blue-50','text-blue-500'] : ($isImg ? ['bg-purple-50','text-purple-500'] : ['bg-gray-100','text-gray-400']));
                @endphp
                <li class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50/60 rounded-lg transition-colors">
                    <!-- Icon -->
                    <div class="w-10 h-10 rounded-xl {{ $icon[0] }} flex items-center justify-center flex-shrink-0">
                        @if($isDoc)
                        <svg class="w-5 h-5 {{ $icon[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        @elseif($isWord)
                        <svg class="w-5 h-5 {{ $icon[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        @elseif($isImg)
                        <svg class="w-5 h-5 {{ $icon[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        @else
                        <svg class="w-5 h-5 {{ $icon[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $doc->label ?: $doc->file_name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $doc->file_name }}
                            <span class="mx-1">·</span>{{ $doc->formatted_size }}
                            <span class="mx-1">·</span>{{ $doc->uploaded_at->format('d.m.Y') }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('employees.documents.download', [$employee, $doc]) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download
                        </a>
                        @if(session('user_role') !== 'KITA_STAFF')
                        <form action="{{ route('employees.documents.destroy', [$employee, $doc]) }}" method="POST"
                              onsubmit="return confirm('Dokument löschen?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        <!-- ── Schulungen ─────────────────────────────────────────────────── -->
        <div x-show="tab==='schulungen'" x-cloak>
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Schulungsnachweise</h3>
                @if(session('user_role') !== 'KITA_STAFF')
                <a href="{{ route('employees.training.create', $employee) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Schulung eintragen
                </a>
                @endif
            </div>

            @if($employee->trainingCompletions->isEmpty())
            <div class="p-10 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm">Noch keine Schulungen eingetragen.</p>
            </div>
            @else
            <ul class="divide-y divide-gray-50 px-2 py-2">
                @foreach($employee->trainingCompletions->sortByDesc('completed_date') as $tc)
                @php
                    $status = $tc->expiryStatus();
                    $statusCfg = match($status) {
                        'expired'  => ['bg-red-100 text-red-800', 'Abgelaufen'],
                        'expiring' => ['bg-yellow-100 text-yellow-800', 'Läuft bald ab'],
                        'valid'    => ['bg-green-100 text-green-800', 'Gültig'],
                        default    => ['bg-gray-100 text-gray-600', 'Kein Ablauf'],
                    };
                    $daysLeft = $tc->expiry_date ? now()->diffInDays($tc->expiry_date, false) : null;
                @endphp
                <li class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50/60 rounded-lg transition-colors">
                    <div class="w-10 h-10 rounded-xl {{ $tc->category->is_first_aid ? 'bg-red-50' : 'bg-indigo-50' }} flex items-center justify-center flex-shrink-0">
                        @if($tc->category->is_first_aid)
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-medium text-gray-800">{{ $tc->category->name }}</p>
                            <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $statusCfg[0] }}">{{ $statusCfg[1] }}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Absolviert: {{ $tc->completed_date->format('d.m.Y') }}
                            @if($tc->expiry_date)
                            · Gültig bis: {{ $tc->expiry_date->format('d.m.Y') }}
                            @if($daysLeft !== null)
                            <span class="{{ $daysLeft < 0 ? 'text-red-500' : ($daysLeft < 60 ? 'text-yellow-600' : 'text-gray-400') }}">
                                ({{ $daysLeft < 0 ? abs($daysLeft) . ' Tage überfällig' : $daysLeft . ' Tage verbleibend' }})
                            </span>
                            @endif
                            @endif
                        </p>
                        @if($tc->notes)
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $tc->notes }}</p>
                        @endif
                    </div>

                    @if(session('user_role') !== 'KITA_STAFF')
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('training.completions.edit', $tc) }}"
                           class="px-2.5 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Bearb.
                        </a>
                        <form action="{{ route('training.completions.destroy', $tc) }}" method="POST"
                              onsubmit="return confirm('Schulungseintrag löschen?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-2.5 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                ✕
                            </button>
                        </form>
                    </div>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>

</div>
@endsection
