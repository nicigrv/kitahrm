<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\TrainingCategory;
use App\Models\TrainingCompletion;
use App\Models\Kita;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TrainingCompletionController extends Controller
{
    private function assertKitaAccess(Employee $employee): void
    {
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');

        if ($userRole !== 'ADMIN' && $employee->kita_id !== $userKitaId) {
            abort(403, 'Zugriff verweigert.');
        }
    }

    public function index(Employee $employee)
    {
        $this->assertKitaAccess($employee);
        $completions = $employee->trainingCompletions()->with('category')->orderByDesc('completed_date')->get();
        $categories = TrainingCategory::active()->ordered()->get();
        return view('employees.show', compact('employee', 'completions', 'categories'));
    }

    public function create(Employee $employee)
    {
        $this->assertKitaAccess($employee);
        $categories = TrainingCategory::active()->ordered()->get();
        return view('training.completion-form', compact('employee', 'categories'));
    }

    public function store(Request $request)
    {
        $employee = Employee::findOrFail($request->employee_id);
        $this->assertKitaAccess($employee);

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'category_id' => 'required|exists:training_categories,id',
            'completed_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:completed_date',
            'notes' => 'nullable|string|max:1000',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'employee_id.required' => 'Mitarbeiter ist erforderlich.',
            'category_id.required' => 'Schulungskategorie ist erforderlich.',
            'completed_date.required' => 'Abschlussdatum ist erforderlich.',
            'expiry_date.after_or_equal' => 'Ablaufdatum muss nach dem Abschlussdatum liegen.',
            'certificate.mimes' => 'Erlaubte Dateitypen: PDF, JPG, PNG.',
            'certificate.max' => 'Die Datei darf maximal 10 MB groß sein.',
        ]);

        $certificatePath = null;
        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $ext = $file->getClientOriginalExtension();
            $uuid = Str::uuid()->toString();
            $certificatePath = "certificates/{$employee->id}/{$uuid}.{$ext}";
            Storage::disk('local')->put($certificatePath, file_get_contents($file->getRealPath()));
        }

        TrainingCompletion::create([
            'employee_id' => $request->employee_id,
            'category_id' => $request->category_id,
            'completed_date' => $request->completed_date,
            'expiry_date' => $request->expiry_date,
            'notes' => $request->notes,
            'certificate_path' => $certificatePath,
        ]);

        return redirect()->route('employees.show', $employee)->with('success', 'Schulung wurde erfolgreich eingetragen.');
    }

    public function edit(TrainingCompletion $completion)
    {
        $employee = $completion->employee;
        $this->assertKitaAccess($employee);
        $categories = TrainingCategory::active()->ordered()->get();
        return view('training.completion-form', compact('employee', 'categories', 'completion'));
    }

    public function update(Request $request, TrainingCompletion $completion)
    {
        $employee = $completion->employee;
        $this->assertKitaAccess($employee);

        $request->validate([
            'category_id' => 'required|exists:training_categories,id',
            'completed_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:completed_date',
            'notes' => 'nullable|string|max:1000',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'category_id.required' => 'Schulungskategorie ist erforderlich.',
            'completed_date.required' => 'Abschlussdatum ist erforderlich.',
            'expiry_date.after_or_equal' => 'Ablaufdatum muss nach dem Abschlussdatum liegen.',
            'certificate.mimes' => 'Erlaubte Dateitypen: PDF, JPG, PNG.',
            'certificate.max' => 'Die Datei darf maximal 10 MB groß sein.',
        ]);

        $certificatePath = $completion->certificate_path;
        if ($request->hasFile('certificate')) {
            if ($certificatePath) {
                Storage::disk('local')->delete($certificatePath);
            }
            $file = $request->file('certificate');
            $ext = $file->getClientOriginalExtension();
            $uuid = Str::uuid()->toString();
            $certificatePath = "certificates/{$employee->id}/{$uuid}.{$ext}";
            Storage::disk('local')->put($certificatePath, file_get_contents($file->getRealPath()));
        }

        $completion->update([
            'category_id' => $request->category_id,
            'completed_date' => $request->completed_date,
            'expiry_date' => $request->expiry_date,
            'notes' => $request->notes,
            'certificate_path' => $certificatePath,
        ]);

        return redirect()->route('employees.show', $employee)->with('success', 'Schulung wurde erfolgreich aktualisiert.');
    }

    public function destroy(TrainingCompletion $completion)
    {
        $employee = $completion->employee;
        $this->assertKitaAccess($employee);

        if ($completion->certificate_path) {
            Storage::disk('local')->delete($completion->certificate_path);
        }

        $completion->delete();
        return redirect()->route('employees.show', $employee)->with('success', 'Schulungseintrag wurde gelöscht.');
    }

    public function matrix(Request $request)
    {
        $userRole = session('user_role');
        $userKitaId = session('user_kita_id');

        if ($userRole === 'ADMIN') {
            $kitas = Kita::orderBy('name')->get();
            $selectedKitaId = $request->kita_id ?? ($kitas->first() ? $kitas->first()->id : null);
        } else {
            $kitas = Kita::where('id', $userKitaId)->get();
            $selectedKitaId = $userKitaId;
        }

        $categories = TrainingCategory::active()->ordered()->get();

        $employees = collect();
        $selectedKita = null;
        if ($selectedKitaId) {
            $selectedKita = Kita::find($selectedKitaId);
            if ($selectedKita) {
                if ($userRole !== 'ADMIN' && $selectedKita->id !== $userKitaId) {
                    abort(403, 'Zugriff verweigert.');
                }
                $employees = $selectedKita->employees()->where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get();
            }
        }

        // Build matrix: employee_id => category_id => latest completion
        $matrix = [];
        if ($employees->isNotEmpty() && $categories->isNotEmpty()) {
            $employeeIds = $employees->pluck('id');
            $categoryIds = $categories->pluck('id');

            $completions = TrainingCompletion::whereIn('employee_id', $employeeIds)
                ->whereIn('category_id', $categoryIds)
                ->orderByDesc('completed_date')
                ->get();

            foreach ($employees as $employee) {
                $matrix[$employee->id] = [];
                foreach ($categories as $category) {
                    $completion = $completions->where('employee_id', $employee->id)
                        ->where('category_id', $category->id)
                        ->first();
                    $matrix[$employee->id][$category->id] = $completion;
                }
            }
        }

        return view('training.matrix', compact('kitas', 'selectedKita', 'selectedKitaId', 'categories', 'employees', 'matrix'));
    }
}
