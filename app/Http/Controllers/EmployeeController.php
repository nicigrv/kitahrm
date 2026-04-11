<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Kita;

class EmployeeController extends Controller
{
    private function assertKitaAccess(Employee $employee): void
    {
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');

        if ($userRole !== 'ADMIN' && $employee->kita_id !== $userKitaId) {
            abort(403, 'Zugriff verweigert. Dieser Mitarbeiter gehört nicht zu Ihrer Kita.');
        }
    }

    public function index(Request $request)
    {
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');

        $query = Employee::with('kita')->orderBy('last_name')->orderBy('first_name');

        if ($userRole !== 'ADMIN') {
            $query->where('kita_id', $userKitaId);
        } elseif ($request->filled('kita_id')) {
            $query->where('kita_id', $request->kita_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $employees = $query->get();
        $kitas = $userRole === 'ADMIN' ? Kita::orderBy('name')->get() : collect();

        return view('employees.index', compact('employees', 'kitas'));
    }

    public function create()
    {
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');
        $kitas = $userRole === 'ADMIN' ? Kita::orderBy('name')->get() : Kita::where('id', $userKitaId)->get();
        $contractTypes = Employee::contractTypeOptions();
        return view('employees.create', compact('kitas', 'contractTypes'));
    }

    public function store(Request $request)
    {
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'position' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'contract_type' => 'required|in:UNBEFRISTET,BEFRISTET,MINIJOB,AUSBILDUNG,PRAKTIKUM,ELTERNZEIT',
            'weekly_hours' => 'required|numeric|min:0|max:80',
            'kita_id' => 'required|exists:kitas,id',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ], [
            'first_name.required' => 'Vorname ist erforderlich.',
            'last_name.required' => 'Nachname ist erforderlich.',
            'start_date.required' => 'Eintrittsdatum ist erforderlich.',
            'contract_type.required' => 'Vertragsart ist erforderlich.',
            'weekly_hours.required' => 'Wochenstunden sind erforderlich.',
            'kita_id.required' => 'Kita ist erforderlich.',
        ]);

        if ($userRole !== 'ADMIN') {
            $request->merge(['kita_id' => $userKitaId]);
        }

        $data = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'address',
            'birth_date', 'position', 'start_date', 'end_date',
            'contract_type', 'weekly_hours', 'kita_id', 'notes',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $employee = Employee::create($data);

        return redirect()->route('employees.show', $employee)->with('success', 'Mitarbeiter wurde erfolgreich angelegt.');
    }

    public function show(Employee $employee)
    {
        $this->assertKitaAccess($employee);
        $employee->load(['kita', 'documents', 'trainingCompletions.category']);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $this->assertKitaAccess($employee);
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');
        $kitas = $userRole === 'ADMIN' ? Kita::orderBy('name')->get() : Kita::where('id', $userKitaId)->get();
        $contractTypes = Employee::contractTypeOptions();
        return view('employees.edit', compact('employee', 'kitas', 'contractTypes'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->assertKitaAccess($employee);
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'position' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'contract_type' => 'required|in:UNBEFRISTET,BEFRISTET,MINIJOB,AUSBILDUNG,PRAKTIKUM,ELTERNZEIT',
            'weekly_hours' => 'required|numeric|min:0|max:80',
            'kita_id' => 'required|exists:kitas,id',
            'notes' => 'nullable|string|max:2000',
        ], [
            'first_name.required' => 'Vorname ist erforderlich.',
            'last_name.required' => 'Nachname ist erforderlich.',
            'start_date.required' => 'Eintrittsdatum ist erforderlich.',
            'contract_type.required' => 'Vertragsart ist erforderlich.',
            'weekly_hours.required' => 'Wochenstunden sind erforderlich.',
            'kita_id.required' => 'Kita ist erforderlich.',
        ]);

        if ($userRole !== 'ADMIN') {
            $request->merge(['kita_id' => $userKitaId]);
        }

        $data = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'address',
            'birth_date', 'position', 'start_date', 'end_date',
            'contract_type', 'weekly_hours', 'kita_id', 'notes',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $employee->update($data);

        return redirect()->route('employees.show', $employee)->with('success', 'Mitarbeiter wurde erfolgreich aktualisiert.');
    }

    public function destroy(Employee $employee)
    {
        $this->assertKitaAccess($employee);
        $name = $employee->full_name;
        $employee->delete();
        return redirect()->route('employees.index')->with('success', "Mitarbeiter \"{$name}\" wurde gelöscht.");
    }
}
