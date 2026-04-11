<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KitaEvent;
use App\Models\Kita;
use Carbon\Carbon;

class KitaEventController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) ($request->get('year',  now()->year));
        $month = (int) ($request->get('month', now()->month));

        // Normalise overflow
        if ($month < 1)  { $month = 12; $year--; }
        if ($month > 12) { $month = 1;  $year++; }

        $allKitas = Kita::orderBy('name')->get();

        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        // Fetch events that overlap the current month
        $events = KitaEvent::with('kita')
            ->where(function ($q) use ($start, $end) {
                // single-day or start falls in month
                $q->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                  // or multi-day event spans into the month
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->whereNotNull('end_date')
                         ->where('date', '<=', $end->toDateString())
                         ->where('end_date', '>=', $start->toDateString());
                  });
            })
            ->get();

        // Build a map: 'Y-m-d' => [KitaEvent, ...]
        $eventsByDate = [];
        foreach ($events as $event) {
            $cur  = $event->date->copy();
            $last = $event->end_date ? $event->end_date->copy() : $event->date->copy();
            while ($cur->lte($last)) {
                $ds = $cur->format('Y-m-d');
                if ($ds >= $start->toDateString() && $ds <= $end->toDateString()) {
                    $eventsByDate[$ds][] = $event;
                }
                $cur->addDay();
            }
        }

        $upcomingEvents = KitaEvent::with('kita')
            ->where('date', '>=', today())
            ->orderBy('date')
            ->limit(12)
            ->get();

        $eventTypes = KitaEvent::typeOptions();

        return view('calendar.index', compact(
            'year', 'month', 'allKitas', 'eventsByDate', 'upcomingEvents', 'eventTypes'
        ));
    }

    public function store(Request $request)
    {
        $userRole = session('user_role');
        if ($userRole === 'KITA_STAFF') abort(403);

        $rules = [
            'date'        => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:date',
            'event_type'  => 'required|in:SCHLIESSTAG,KURZE_ZEITEN,FORTBILDUNG,INFO',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_time'  => 'nullable|regex:/^\d{2}:\d{2}$/',
            'end_time'    => 'nullable|regex:/^\d{2}:\d{2}$/',
        ];
        if ($userRole === 'ADMIN') {
            $rules['kita_id'] = 'required|exists:kitas,id';
        }

        $request->validate($rules);

        $kitaId = $userRole === 'ADMIN' ? $request->kita_id : (int) session('user_kita_id');

        KitaEvent::create([
            'kita_id'     => $kitaId,
            'date'        => $request->date,
            'end_date'    => $request->end_date ?: null,
            'event_type'  => $request->event_type,
            'title'       => $request->title,
            'description' => $request->description ?: null,
            'start_time'  => $request->start_time ?: null,
            'end_time'    => $request->end_time ?: null,
        ]);

        $year  = (int) date('Y', strtotime($request->date));
        $month = (int) date('n', strtotime($request->date));

        return redirect()
            ->route('calendar.index', compact('year', 'month'))
            ->with('success', 'Eintrag wurde gespeichert.');
    }

    public function destroy(KitaEvent $event)
    {
        $userRole = session('user_role');
        if ($userRole === 'KITA_STAFF') abort(403);
        if ($userRole !== 'ADMIN' && (int)$event->kita_id !== (int)session('user_kita_id')) abort(403);

        $event->delete();
        return back()->with('success', 'Eintrag wurde gelöscht.');
    }
}
