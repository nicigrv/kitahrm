@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Mitarbeiter (aktiv)</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalEmployees }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Kitas</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalKitas }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Ablaufend (60 Tage)</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $expiringCount }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Abgelaufen</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $expiredCount }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- First Aid Status Cards -->
    <div>
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Erste-Hilfe-Abdeckung</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            @foreach($kitaStats as $stat)
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 {{ $stat['first_aid_ok'] ? 'border-green-500' : 'border-red-500' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $stat['kita']->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $stat['kita']->short_code }}</p>
                    </div>
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $stat['first_aid_ok'] ? 'bg-green-100' : 'bg-red-100' }} flex-shrink-0">
                        @if($stat['first_aid_ok'])
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        @else
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        @endif
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Mitarbeiter:</span>
                        <span class="font-medium text-gray-700">{{ $stat['employee_count'] }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Erste Hilfe:</span>
                        <span class="font-medium {{ $stat['first_aid_ok'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $stat['first_aid_count'] }} / {{ $stat['kita']->min_first_aid }} Min.
                        </span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Wochenstunden:</span>
                        <span class="font-medium text-gray-700">{{ number_format($stat['weekly_hours'], 1, ',', '.') }} h</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bar Chart: Weekly Hours per Kita -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Wochenstunden je Kita</h3>
            <div class="relative h-64">
                <canvas id="hoursChart"></canvas>
            </div>
        </div>

        <!-- Pie Chart: Contract Types -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Vertragsarten</h3>
            <div class="relative h-64">
                <canvas id="contractChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Expiry Alerts Table -->
    @if($expiryAlerts->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-800">Ablaufende Schulungszertifikate</h3>
            <p class="text-sm text-gray-500 mt-0.5">Zertifikate, die abgelaufen sind oder in den nächsten 60 Tagen ablaufen</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mitarbeiter</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kita</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Schulung</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ablaufdatum</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($expiryAlerts as $completion)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="/employees/{{ $completion->employee_id }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                {{ $completion->employee->full_name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $completion->employee->kita->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $completion->category->name }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $completion->expiry_date->format('d.m.Y') }}</td>
                        <td class="px-6 py-4">
                            @if($completion->expiry_date->isPast())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Abgelaufen
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Läuft ab in {{ $completion->expiry_date->diffInDays(now()) }} Tagen
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
        <svg class="w-12 h-12 text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-gray-600 font-medium">Alle Schulungszertifikate sind aktuell!</p>
        <p class="text-gray-400 text-sm mt-1">Keine ablaufenden Zertifikate in den nächsten 60 Tagen.</p>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const kitaLabels = @json($chartKitaLabels);
    const weeklyHoursData = @json($chartWeeklyHours);
    const employeeCountData = @json($chartEmployeeCounts);
    const pieLabels = @json($pieLabels);
    const pieData = @json($pieData);

    // Bar Chart: Weekly Hours
    const hoursCtx = document.getElementById('hoursChart').getContext('2d');
    new Chart(hoursCtx, {
        type: 'bar',
        data: {
            labels: kitaLabels,
            datasets: [
                {
                    label: 'Wochenstunden',
                    data: weeklyHoursData,
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Mitarbeiter',
                    data: employeeCountData,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Wochenstunden' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Mitarbeiter' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    // Pie Chart: Contract Types
    const contractCtx = document.getElementById('contractChart').getContext('2d');
    new Chart(contractCtx, {
        type: 'doughnut',
        data: {
            labels: pieLabels,
            datasets: [{
                data: pieData,
                backgroundColor: [
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 12, font: { size: 11 } }
                }
            }
        }
    });
</script>
@endpush
