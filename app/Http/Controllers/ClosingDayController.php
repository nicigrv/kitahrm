<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kita;
use App\Models\KitaClosingDay;

class ClosingDayController extends Controller
{
    private function assertAccess(Kita $kita): void
    {
        $role   = session('user_role');
        $kitaId = session('user_kita_id');

        if ($role !== 'ADMIN' && (int)$kita->id !== (int)$kitaId) {
            abort(403, 'Zugriff verweigert.');
        }
        if ($role === 'KITA_STAFF') {
            abort(403, 'Keine Berechtigung zum Bearbeiten.');
        }
    }

    public function calendar(Kita $kita)
    {
        $role   = session('user_role');
        $kitaId = session('user_kita_id');
        if ($role !== 'ADMIN' && (int)$kita->id !== (int)$kitaId) abort(403);

        $year  = (int) request('year',  now()->year);
        $month = (int) request('month', now()->month);

        // Clamp month
        if ($month < 1)  { $month = 12; $year--; }
        if ($month > 12) { $month = 1;  $year++; }

        $closingDays = $kita->closingDays()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get()
            ->keyBy(fn($d) => $d->date->format('Y-m-d'));

        // Upcoming closing days for list view (next 12 months)
        $upcomingDays = $kita->closingDays()
            ->where('date', '>=', now()->startOfDay())
            ->orderBy('date')
            ->limit(30)
            ->get();

        return view('kitas.calendar', compact('kita', 'year', 'month', 'closingDays', 'upcomingDays'));
    }

    public function store(Request $request, Kita $kita)
    {
        $this->assertAccess($kita);

        $request->validate([
            'date'  => 'required|date',
            'label' => 'nullable|string|max:255',
        ]);

        KitaClosingDay::updateOrCreate(
            ['kita_id' => $kita->id, 'date' => $request->date],
            ['label' => $request->label]
        );

        return back()->with('success', 'Schließtag eingetragen.');
    }

    public function destroy(Kita $kita, KitaClosingDay $closingDay)
    {
        $this->assertAccess($kita);

        if ((int)$closingDay->kita_id !== (int)$kita->id) abort(404);

        $closingDay->delete();
        return back()->with('success', 'Schließtag entfernt.');
    }
}
