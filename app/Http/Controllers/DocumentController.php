<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
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
        $documents = $employee->documents()->orderByDesc('uploaded_at')->get();
        return view('employees.documents', compact('employee', 'documents'));
    }

    public function store(Request $request, Employee $employee)
    {
        $this->assertKitaAccess($employee);

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:10240',
            'label' => 'nullable|string|max:255',
        ], [
            'document.required' => 'Bitte wählen Sie eine Datei aus.',
            'document.file' => 'Die hochgeladene Datei ist ungültig.',
            'document.mimes' => 'Erlaubte Dateitypen: PDF, JPG, PNG, DOCX.',
            'document.max' => 'Die Datei darf maximal 10 MB groß sein.',
        ]);

        $file = $request->file('document');
        $ext = $file->getClientOriginalExtension();
        $uuid = Str::uuid()->toString();
        $storagePath = "documents/{$employee->id}/{$uuid}.{$ext}";

        Storage::disk('local')->put($storagePath, file_get_contents($file->getRealPath()));

        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'file_name' => $file->getClientOriginalName(),
            'storage_path' => $storagePath,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'label' => $request->label,
            'uploaded_at' => now(),
        ]);

        return redirect()->route('employees.documents.index', $employee)->with('success', 'Dokument wurde erfolgreich hochgeladen.');
    }

    public function download(Employee $employee, EmployeeDocument $document)
    {
        $this->assertKitaAccess($employee);

        if ($document->employee_id !== $employee->id) {
            abort(404);
        }

        $path = Storage::disk('local')->path($document->storage_path);

        if (!file_exists($path)) {
            abort(404, 'Datei nicht gefunden.');
        }

        return response()->download($path, $document->file_name, [
            'Content-Type' => $document->mime_type,
        ]);
    }

    public function destroy(Employee $employee, EmployeeDocument $document)
    {
        $this->assertKitaAccess($employee);

        if ($document->employee_id !== $employee->id) {
            abort(404);
        }

        Storage::disk('local')->delete($document->storage_path);
        $document->delete();

        return redirect()->route('employees.documents.index', $employee)->with('success', 'Dokument wurde gelöscht.');
    }
}
