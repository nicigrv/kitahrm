<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kita;
use App\Models\Employee;
use App\Models\TrainingCompletion;
use App\Models\TrainingCategory;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');

        if ($userRole === 'ADMIN') {
            $kitas = Kita::all();
        } else {
            $kitas = Kita::where('id', $userKitaId)->get();
        }

        $kitaStats = [];
        foreach ($kitas as $kita) {
            $activeEmployees = $kita->employees()->where('is_active', true)->get();
            $totalWeeklyHours = $activeEmployees->sum('weekly_hours');

            $firstAidCategories = TrainingCategory::where('is_first_aid', true)->where('is_active', true)->pluck('id');
            $firstAidCount = 0;
            foreach ($activeEmployees as $emp) {
                $hasValid = TrainingCompletion::where('employee_id', $emp->id)
                    ->whereIn('category_id', $firstAidCategories)
                    ->where(function ($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>', now());
                    })
                    ->exists();
                if ($hasValid) $firstAidCount++;
            }

            $kitaStats[] = [
                'kita' => $kita,
                'employee_count' => $activeEmployees->count(),
                'weekly_hours' => $totalWeeklyHours,
                'first_aid_count' => $firstAidCount,
                'first_aid_ok' => $firstAidCount >= $kita->min_first_aid,
            ];
        }

        // Expiry alerts (expiring within 60 days or already expired)
        $employeeIds = Employee::whereIn('kita_id', $kitas->pluck('id'))->where('is_active', true)->pluck('id');
        $expiryAlerts = TrainingCompletion::with(['employee.kita', 'category'])
            ->whereIn('employee_id', $employeeIds)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(60))
            ->orderBy('expiry_date')
            ->get();

        $expiredCount = $expiryAlerts->filter(fn($c) => $c->expiry_date->isPast())->count();
        $expiringCount = $expiryAlerts->filter(fn($c) => !$c->expiry_date->isPast())->count();

        // Chart data: staffing per kita (bar chart)
        $chartKitaLabels = $kitas->pluck('name')->toArray();
        $chartWeeklyHours = array_column($kitaStats, 'weekly_hours');
        $chartEmployeeCounts = array_column($kitaStats, 'employee_count');

        // Contract type distribution (pie chart)
        if ($userRole === 'ADMIN') {
            $allEmployees = Employee::where('is_active', true)->get();
        } else {
            $allEmployees = Employee::where('kita_id', $userKitaId)->where('is_active', true)->get();
        }

        $contractTypes = $allEmployees->groupBy('contract_type')->map->count();
        $contractLabels = [
            'UNBEFRISTET' => 'Unbefristet',
            'BEFRISTET' => 'Befristet',
            'MINIJOB' => 'Minijob',
            'AUSBILDUNG' => 'Ausbildung',
            'PRAKTIKUM' => 'Praktikum',
            'ELTERNZEIT' => 'Elternzeit',
        ];
        $pieLabels = [];
        $pieData = [];
        foreach ($contractTypes as $type => $count) {
            $pieLabels[] = $contractLabels[$type] ?? $type;
            $pieData[] = $count;
        }

        $totalEmployees = $allEmployees->count();
        $totalKitas = $kitas->count();

        return view('dashboard.index', compact(
            'kitaStats',
            'expiryAlerts',
            'expiredCount',
            'expiringCount',
            'chartKitaLabels',
            'chartWeeklyHours',
            'chartEmployeeCounts',
            'pieLabels',
            'pieData',
            'totalEmployees',
            'totalKitas'
        ));
    }
}
