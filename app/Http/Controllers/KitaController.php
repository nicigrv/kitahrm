<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kita;
use App\Models\TrainingCategory;
use App\Models\TrainingCompletion;

class KitaController extends Controller
{
    public function index()
    {
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');

        if ($userRole === 'ADMIN') {
            $kitas = Kita::withCount(['employees' => function ($q) {
                $q->where('is_active', true);
            }])->get();
        } else {
            $kitas = Kita::where('id', $userKitaId)->withCount(['employees' => function ($q) {
                $q->where('is_active', true);
            }])->get();
        }

        $firstAidCategories = TrainingCategory::where('is_first_aid', true)->where('is_active', true)->pluck('id');

        $kitaData = $kitas->map(function ($kita) use ($firstAidCategories) {
            $activeEmployees = $kita->employees()->where('is_active', true)->get();
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
            return [
                'kita' => $kita,
                'employee_count' => $activeEmployees->count(),
                'first_aid_count' => $firstAidCount,
                'first_aid_ok' => $firstAidCount >= $kita->min_first_aid,
            ];
        });

        return view('kitas.index', compact('kitaData'));
    }

    public function show(Kita $kita)
    {
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');

        if ($userRole !== 'ADMIN' && $kita->id !== $userKitaId) {
            abort(403, 'Zugriff verweigert.');
        }

        $employees = $kita->employees()->where('is_active', true)->orderBy('last_name')->get();

        $firstAidCategories = TrainingCategory::where('is_first_aid', true)->where('is_active', true)->pluck('id');
        $firstAidCount = 0;
        foreach ($employees as $emp) {
            $hasValid = TrainingCompletion::where('employee_id', $emp->id)
                ->whereIn('category_id', $firstAidCategories)
                ->where(function ($q) {
                    $q->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>', now());
                })
                ->exists();
            if ($hasValid) $firstAidCount++;
        }

        $firstAidOk = $firstAidCount >= $kita->min_first_aid;

        return view('kitas.show', compact('kita', 'employees', 'firstAidCount', 'firstAidOk'));
    }

    public function edit(Kita $kita)
    {
        return view('kitas.edit', compact('kita'));
    }

    public function update(Request $request, Kita $kita)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_code' => 'required|string|max:20|unique:kitas,short_code,' . $kita->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'min_first_aid' => 'required|integer|min:0',
        ], [
            'name.required' => 'Name ist erforderlich.',
            'short_code.required' => 'Kurzcode ist erforderlich.',
            'short_code.unique' => 'Dieser Kurzcode wird bereits verwendet.',
            'min_first_aid.required' => 'Mindestanzahl Erste-Hilfe ist erforderlich.',
            'min_first_aid.integer' => 'Mindestanzahl muss eine ganze Zahl sein.',
            'min_first_aid.min' => 'Mindestanzahl muss mindestens 0 sein.',
        ]);

        $kita->update($request->only(['name', 'short_code', 'address', 'phone', 'email', 'min_first_aid']));

        return redirect()->route('kitas.show', $kita)->with('success', 'Kita wurde erfolgreich aktualisiert.');
    }
}
