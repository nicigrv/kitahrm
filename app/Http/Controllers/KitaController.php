<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kita;
use App\Models\TrainingCategory;
use App\Models\TrainingCompletion;
use App\Models\KitaTrainingRequirement;

class KitaController extends Controller
{
    public function index()
    {
        $userRole   = session('user_role');
        $userKitaId = session('user_kita_id');

        if ($userRole === 'ADMIN') {
            $kitas = Kita::withCount(['employees' => fn($q) => $q->where('is_active', true)])->get();
        } else {
            $kitas = Kita::where('id', $userKitaId)
                ->withCount(['employees' => fn($q) => $q->where('is_active', true)])
                ->get();
        }

        $firstAidCategories = TrainingCategory::where('is_first_aid', true)->where('is_active', true)->pluck('id');

        $kitaData = $kitas->map(function ($kita) use ($firstAidCategories) {
            $activeEmployees = $kita->employees()->where('is_active', true)->get();
            $firstAidCount   = 0;
            foreach ($activeEmployees as $emp) {
                $hasValid = TrainingCompletion::where('employee_id', $emp->id)
                    ->whereIn('category_id', $firstAidCategories)
                    ->where(fn($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now()))
                    ->exists();
                if ($hasValid) $firstAidCount++;
            }
            return [
                'kita'            => $kita,
                'employee_count'  => $activeEmployees->count(),
                'first_aid_count' => $firstAidCount,
                'first_aid_ok'    => $firstAidCount >= $kita->min_first_aid,
                'staff_ok'        => $kita->min_staff_total === 0 || $activeEmployees->count() >= $kita->min_staff_total,
            ];
        });

        return view('kitas.index', compact('kitaData'));
    }

    public function create()
    {
        return view('kitas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'short_code'        => 'required|string|max:20|unique:kitas,short_code',
            'address'           => 'nullable|string|max:500',
            'phone'             => 'nullable|string|max:50',
            'email'             => 'nullable|email|max:255',
            'min_first_aid'     => 'required|integer|min:0',
            'min_staff_total'   => 'required|integer|min:0',
            'min_skilled_staff' => 'required|integer|min:0',
            'notes'             => 'nullable|string|max:2000',
        ], [
            'name.required'         => 'Name ist erforderlich.',
            'short_code.required'   => 'Kurzcode ist erforderlich.',
            'short_code.unique'     => 'Dieser Kurzcode wird bereits verwendet.',
            'min_first_aid.required'=> 'Mindestanzahl Ersthelfer ist erforderlich.',
        ]);

        $kita = Kita::create($request->only([
            'name', 'short_code', 'address', 'phone', 'email',
            'min_first_aid', 'min_staff_total', 'min_skilled_staff', 'notes',
        ]));

        return redirect()->route('kitas.show', $kita)->with('success', 'Kita wurde erfolgreich angelegt.');
    }

    public function show(Kita $kita)
    {
        $userRole   = session('user_role');
        $userKitaId = session('user_kita_id');

        if ($userRole !== 'ADMIN' && $kita->id !== $userKitaId) {
            abort(403, 'Zugriff verweigert.');
        }

        $employees          = $kita->employees()->where('is_active', true)->orderBy('last_name')->get();
        $firstAidCategories = TrainingCategory::where('is_first_aid', true)->where('is_active', true)->pluck('id');
        $firstAidCount      = 0;
        foreach ($employees as $emp) {
            $hasValid = TrainingCompletion::where('employee_id', $emp->id)
                ->whereIn('category_id', $firstAidCategories)
                ->where(fn($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now()))
                ->exists();
            if ($hasValid) $firstAidCount++;
        }

        $firstAidOk = $firstAidCount >= $kita->min_first_aid;

        // Training requirements with current counts
        $allCategories       = TrainingCategory::where('is_active', true)->get();
        $trainingRequirements = $kita->trainingRequirements()->with('category')->get()->keyBy('category_id');

        $trainingStatus = $allCategories->map(function ($cat) use ($employees, $trainingRequirements) {
            $req      = $trainingRequirements->get($cat->id);
            $minCount = $req ? $req->min_count : 0;
            $count    = 0;
            foreach ($employees as $emp) {
                $valid = TrainingCompletion::where('employee_id', $emp->id)
                    ->where('category_id', $cat->id)
                    ->where(fn($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now()))
                    ->exists();
                if ($valid) $count++;
            }
            return ['category' => $cat, 'min_count' => $minCount, 'current_count' => $count, 'ok' => $minCount === 0 || $count >= $minCount];
        })->filter(fn($ts) => $ts['min_count'] > 0 || $ts['current_count'] > 0);

        $managers = $kita->users()->where('role', 'KITA_MANAGER')->get();

        return view('kitas.show', compact('kita', 'employees', 'firstAidCount', 'firstAidOk', 'trainingStatus', 'managers'));
    }

    public function edit(Kita $kita)
    {
        $allCategories        = TrainingCategory::where('is_active', true)->orderBy('name')->get();
        $trainingRequirements = $kita->trainingRequirements()->get()->keyBy('category_id');
        return view('kitas.edit', compact('kita', 'allCategories', 'trainingRequirements'));
    }

    public function update(Request $request, Kita $kita)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'short_code'             => 'required|string|max:20|unique:kitas,short_code,' . $kita->id,
            'address'                => 'nullable|string|max:500',
            'phone'                  => 'nullable|string|max:50',
            'email'                  => 'nullable|email|max:255',
            'min_first_aid'          => 'required|integer|min:0',
            'min_staff_total'        => 'required|integer|min:0',
            'min_skilled_staff'      => 'required|integer|min:0',
            'notes'                  => 'nullable|string|max:2000',
            'training_requirements'  => 'nullable|array',
            'training_requirements.*'=> 'nullable|integer|min:0',
        ], [
            'name.required'         => 'Name ist erforderlich.',
            'short_code.required'   => 'Kurzcode ist erforderlich.',
            'short_code.unique'     => 'Dieser Kurzcode wird bereits verwendet.',
            'min_first_aid.required'=> 'Mindestanzahl Ersthelfer ist erforderlich.',
        ]);

        $kita->update($request->only([
            'name', 'short_code', 'address', 'phone', 'email',
            'min_first_aid', 'min_staff_total', 'min_skilled_staff', 'notes',
        ]));

        // Sync training requirements
        if ($request->has('training_requirements')) {
            foreach ($request->training_requirements as $categoryId => $minCount) {
                $minCount = (int) $minCount;
                if ($minCount > 0) {
                    KitaTrainingRequirement::updateOrCreate(
                        ['kita_id' => $kita->id, 'category_id' => $categoryId],
                        ['min_count' => $minCount]
                    );
                } else {
                    KitaTrainingRequirement::where('kita_id', $kita->id)
                        ->where('category_id', $categoryId)
                        ->delete();
                }
            }
        }

        return redirect()->route('kitas.show', $kita)->with('success', 'Kita wurde erfolgreich aktualisiert.');
    }

    public function destroy(Kita $kita)
    {
        if ($kita->employees()->exists()) {
            return back()->with('error', 'Kita kann nicht gelöscht werden, da noch Mitarbeiter zugeordnet sind.');
        }
        $name = $kita->name;
        $kita->delete();
        return redirect()->route('kitas.index')->with('success', "Kita \"{$name}\" wurde gelöscht.");
    }
}
