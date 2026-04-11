@extends('layouts.app')

@section('title', 'Kitas')
@section('page-title', 'Kita-Übersicht')

@section('content')
<div class="space-y-4">

    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">{{ count($kitaData) }} Kita(s) gesamt</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kürzel</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Adresse</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Mitarbeiter</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Erste Hilfe</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($kitaData as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="/kitas/{{ $item['kita']->id }}" class="font-semibold text-indigo-600 hover:text-indigo-800">
                                {{ $item['kita']->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-mono font-medium bg-gray-100 text-gray-700 rounded">{{ $item['kita']->short_code }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ $item['kita']->address ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-medium text-gray-800">{{ $item['employee_count'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($item['first_aid_ok'])
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $item['first_aid_count'] }}/{{ $item['kita']->min_first_aid }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $item['first_aid_count'] }}/{{ $item['kita']->min_first_aid }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="/kitas/{{ $item['kita']->id }}"
                                   class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Details</a>
                                @if(session('user_role') === 'ADMIN')
                                <span class="text-gray-300">|</span>
                                <a href="/kitas/{{ $item['kita']->id }}/edit"
                                   class="text-gray-600 hover:text-gray-800 text-xs font-medium">Bearbeiten</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
