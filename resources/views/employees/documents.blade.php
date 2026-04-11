@extends('layouts.app')

@section('title', 'Dokumente - ' . $employee->full_name)
@section('page-title', 'Dokumente: ' . $employee->full_name)

@section('content')
<div class="space-y-4">

    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 space-x-2">
        <a href="/employees" class="hover:text-gray-700">Mitarbeiter</a>
        <span>/</span>
        <a href="/employees/{{ $employee->id }}" class="hover:text-gray-700">{{ $employee->full_name }}</a>
        <span>/</span>
        <span class="text-gray-800">Dokumente</span>
    </nav>

    <!-- Upload Form -->
    @if(session('user_role') !== 'KITA_STAFF')
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Dokument hochladen</h2>
        <form method="POST" action="/employees/{{ $employee->id }}/documents" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="document" class="block text-sm font-medium text-gray-700 mb-1">
                        Datei <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(PDF, JPG, PNG, DOCX – max. 10 MB)</span>
                    </label>
                    <input type="file" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png,.docx" required
                           class="w-full px-3 py-2.5 border {{ $errors->has('document') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('document') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="label" class="block text-sm font-medium text-gray-700 mb-1">Bezeichnung (optional)</label>
                    <input type="text" id="label" name="label" value="{{ old('label') }}" placeholder="z.B. Arbeitsvertrag, Zeugnis..."
                           class="w-full px-4 py-2.5 border {{ $errors->has('label') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('label') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Hochladen
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Document List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-800">Vorhandene Dokumente ({{ $documents->count() }})</h3>
        </div>
        @if($documents->isEmpty())
        <div class="p-8 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>Noch keine Dokumente vorhanden.</p>
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($documents as $doc)
            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                        {{ str_contains($doc->mime_type, 'pdf') ? 'bg-red-50' : (str_contains($doc->mime_type, 'image') ? 'bg-blue-50' : 'bg-gray-50') }}">
                        @if(str_contains($doc->mime_type, 'pdf'))
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        @elseif(str_contains($doc->mime_type, 'image'))
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $doc->label ?: $doc->file_name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $doc->file_name }}
                            &middot; {{ $doc->formatted_size }}
                            &middot; Hochgeladen am {{ $doc->uploaded_at->format('d.m.Y \u\m H:i \U\h\r') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-3 flex-shrink-0">
                    <a href="/employees/{{ $employee->id }}/documents/{{ $doc->id }}/download"
                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Herunterladen
                    </a>
                    @if(session('user_role') !== 'KITA_STAFF')
                    <form action="/employees/{{ $employee->id }}/documents/{{ $doc->id }}" method="POST"
                          onsubmit="return confirm('Dokument wirklich löschen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Löschen
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Back Link -->
    <div>
        <a href="/employees/{{ $employee->id }}" class="text-sm text-indigo-600 hover:text-indigo-800">
            &larr; Zurück zu {{ $employee->full_name }}
        </a>
    </div>

</div>
@endsection
