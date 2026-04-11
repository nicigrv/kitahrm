@extends('layouts.app')

@section('title', 'Benutzerverwaltung')
@section('page-title', 'Benutzerverwaltung')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <p class="text-sm text-gray-500">{{ $users->count() }} Benutzer gesamt</p>
        <a href="/users/create"
           class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Neuen Benutzer anlegen
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Benutzer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rolle</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Einrichtung</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Erstellt</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 {{ $user->id === session('user_id') ? 'bg-indigo-50/30' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 font-semibold text-sm
                                    {{ $user->role === 'ADMIN' ? 'bg-purple-100 text-purple-700' : ($user->role === 'KITA_MANAGER' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        {{ $user->name }}
                                        @if($user->id === session('user_id'))
                                        <span class="ml-1 text-xs text-indigo-500">(Sie)</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @switch($user->role)
                                @case('ADMIN')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Administrator</span>
                                    @break
                                @case('KITA_MANAGER')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Kita-Leitung</span>
                                    @break
                                @case('KITA_STAFF')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Kita-Personal</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            @if($user->kita)
                            <a href="/kitas/{{ $user->kita_id }}" class="text-indigo-600 hover:text-indigo-800">{{ $user->kita->name }}</a>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs">
                            {{ $user->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="/users/{{ $user->id }}/edit"
                                   class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Bearbeiten</a>
                                @if($user->id !== session('user_id'))
                                <span class="text-gray-300">|</span>
                                <form method="POST" action="/users/{{ $user->id }}"
                                      onsubmit="return confirm('Benutzer {{ addslashes($user->name) }} wirklich löschen?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Löschen</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400">Keine Benutzer vorhanden.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Role legend -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Rollenübersicht</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="flex items-start space-x-3 p-3 bg-purple-50 rounded-lg">
                <span class="inline-flex w-7 h-7 rounded-full bg-purple-100 text-purple-700 items-center justify-center text-xs font-bold flex-shrink-0">A</span>
                <div>
                    <p class="text-sm font-medium text-gray-800">Administrator</p>
                    <p class="text-xs text-gray-500">Vollzugriff auf alle Einrichtungen, Benutzer und Einstellungen</p>
                </div>
            </div>
            <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
                <span class="inline-flex w-7 h-7 rounded-full bg-blue-100 text-blue-700 items-center justify-center text-xs font-bold flex-shrink-0">L</span>
                <div>
                    <p class="text-sm font-medium text-gray-800">Kita-Leitung</p>
                    <p class="text-xs text-gray-500">Mitarbeiter und Schulungen der eigenen Einrichtung verwalten</p>
                </div>
            </div>
            <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                <span class="inline-flex w-7 h-7 rounded-full bg-green-100 text-green-700 items-center justify-center text-xs font-bold flex-shrink-0">P</span>
                <div>
                    <p class="text-sm font-medium text-gray-800">Kita-Personal</p>
                    <p class="text-xs text-gray-500">Lesezugriff auf Daten der eigenen Einrichtung</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
