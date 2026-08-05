<?php

namespace App\Http\Controllers;

use App\Models\ComplexActivity;
use App\Models\PoolClosure;
use App\Models\Reservation;
use App\Models\ReservationCredit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class PoolClosureController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = PoolClosure::with(['complexActivity.complex', 'complexActivity.activity'])
            ->latest();

        if (!empty($user->complex_id) && $user->complex_id != 0) {
            $query->whereHas('complexActivity', function ($q) use ($user) {
                $q->where('complex_id', $user->complex_id);
            });
        }

        $poolClosures = $query->paginate(20);

        return view('admin.pool_closures.index', compact('poolClosures'));
    }

    public function create()
    {
        $user = auth()->user();

        $query = ComplexActivity::with(['complex', 'activity']);

        if (!empty($user->complex_id) && $user->complex_id != 0) {
            $query->where('complex_id', $user->complex_id);
        }

        $complexActivities = $query->get();

        return view('admin.pool_closures.create', compact('complexActivities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'complex_activity_id' => 'required|exists:complex_activity,id',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'reason'              => 'nullable|string|max:255',
        ], [
            'complex_activity_id.required' => 'يرجى اختيار المركب والنشاط.',
            'start_date.required'          => 'يرجى تحديد تاريخ ووقت بداية العطل.',
            'end_date.required'            => 'يرجى تحديد تاريخ ووقت نهاية العطل.',
            'end_date.after_or_equal'      => 'تاريخ ووقت نهاية العطل يجب أن يكون بعد أو يساوي تاريخ ووقت البداية.',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate   = Carbon::parse($validated['end_date']);

        $poolClosure = PoolClosure::create([
            'complex_activity_id' => $validated['complex_activity_id'],
            'start_date'          => $startDate,
            'end_date'            => $endDate,
            'closure_date'        => $startDate, // للتوافق مع الأكواد القديمة
            'reason'              => $validated['reason'] ?? null,
            'status'              => 'active',
            'created_by'          => auth()->id(),
        ]);

        return redirect()
            ->route('admin.pool-closures.show', $poolClosure)
            ->with('success', 'تم تسجيل فترة العطل بالتاريخ والوقت بنجاح. يرجى مراجعة الحجوزات المتأثرة قبل توليد الأرصدة.');
    }

    public function show(PoolClosure $poolClosure)
    {
        $poolClosure->load(['complexActivity.complex', 'complexActivity.activity']);

        $impactedReservations = $this->getImpactedReservations($poolClosure);

        $alreadyApplied = $impactedReservations->count() > 0
            && $impactedReservations->every(fn ($item) => !empty($item['already_credited']));

        return view('admin.pool_closures.show', compact(
            'poolClosure',
            'impactedReservations',
            'alreadyApplied'
        ));
    }

    public function edit(PoolClosure $poolClosure)
    {
        $user = auth()->user();

        $query = ComplexActivity::with(['complex', 'activity']);

        if (!empty($user->complex_id) && $user->complex_id != 0) {
            $query->where('complex_id', $user->complex_id);
        }

        $complexActivities = $query->get();

        return view('admin.pool_closures.edit', compact('poolClosure', 'complexActivities'));
    }

    public function update(Request $request, PoolClosure $poolClosure)
    {
        $validated = $request->validate([
            'complex_activity_id' => 'required|exists:complex_activity,id',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'reason'              => 'nullable|string|max:255',
            'status'              => 'required|in:active,cancelled',
        ], [
            'complex_activity_id.required' => 'يرجى اختيار المركب والنشاط.',
            'start_date.required'          => 'يرجى تحديد تاريخ ووقت بداية العطل.',
            'end_date.required'            => 'يرجى تحديد تاريخ ووقت نهاية العطل.',
            'end_date.after_or_equal'      => 'تاريخ ووقت نهاية العطل يجب أن يكون بعد أو يساوي تاريخ ووقت البداية.',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate   = Carbon::parse($validated['end_date']);

        $poolClosure->update([
            'complex_activity_id' => $validated['complex_activity_id'],
            'start_date'          => $startDate,
            'end_date'            => $endDate,
            'closure_date'        => $startDate,
            'reason'              => $validated['reason'] ?? null,
            'status'              => $validated['status'],
        ]);

        return redirect()
            ->route('admin.pool-closures.show', $poolClosure)
            ->with('success', 'تم تعديل فترة العطل بالتاريخ والوقت بنجاح.');
    }

    public function destroy(PoolClosure $poolClosure)
    {
        $poolClosure->delete();

        return redirect()
            ->route('admin.pool-closures.index')
            ->with('success', '✔ تم حذف العطل بنجاح.');
    }

    public function apply(PoolClosure $poolClosure)
    {
        if ($poolClosure->status !== 'active') {
            return back()->with('error', 'هذا العطل ملغى، لا يمكن توليد الأرصدة.');
        }

        $impactedReservations = $this->getImpactedReservations($poolClosure);

        $createdCredits = 0;
        $totalCredit = 0;

        foreach ($impactedReservations as $item) {
            $reservation = $item['reservation'];
            $sessionValue = $item['session_value'];

            if ($sessionValue <= 0) {
                continue;
            }

            foreach ($item['impacted_dates'] as $impactedDate) {
                $credit = ReservationCredit::firstOrCreate(
                    [
                        'reservation_id' => $reservation->id,
                        'closure_date'   => $impactedDate,
                    ],
                    [
                        'user_id'             => $reservation->user_id,
                        'complex_activity_id' => $reservation->complex_activity_id,
                        'credited_amount'     => $sessionValue,
                        'status'              => 'pending',
                        'note'                => 'رصيد تعويضي بسبب عطل في المسبح',
                    ]
                );

                if ($credit->wasRecentlyCreated) {
                    $createdCredits++;
                    $totalCredit += $sessionValue;
                }
            }
        }

        return redirect()
            ->route('admin.pool-closures.show', $poolClosure)
            ->with('success', "تم توليد {$createdCredits} رصيد تعويضي، بمجموع {$totalCredit} دج.");
    }

    private function getImpactedReservations(PoolClosure $poolClosure)
    {
        $closureStart = Carbon::parse($poolClosure->start_date);
        $closureEnd   = Carbon::parse($poolClosure->end_date);

        $reservations = Reservation::with(['user', 'schedule'])
            ->where('complex_activity_id', $poolClosure->complex_activity_id)
            ->whereDate('start_date', '<=', $closureEnd->toDateString())
            ->whereDate('end_date', '>=', $closureStart->toDateString())
            ->where('payment_status', 'paid')
            ->where('statut', 'confirmee')
            ->get();

        $result = collect();

        foreach ($reservations as $reservation) {
            $slots = $this->getReservationSlotsArray($reservation);

            $reservationDays = collect($slots)
                ->pluck('day_number')
                ->filter(fn ($day) => $day !== null)
                ->map(fn ($day) => (int) $day)
                ->unique()
                ->values()
                ->toArray();

            if (empty($reservationDays)) {
                continue;
            }

            $reservationStart = Carbon::parse($reservation->start_date)->startOfDay();
            $reservationEnd   = Carbon::parse($reservation->end_date)->endOfDay();

            $impactStart = $closureStart->greaterThan($reservationStart)
                ? $closureStart->copy()
                : $reservationStart->copy();

            $impactEnd = $closureEnd->lessThan($reservationEnd)
                ? $closureEnd->copy()
                : $reservationEnd->copy();

            if ($impactStart->greaterThan($impactEnd)) {
                continue;
            }

            $impactedDates = [];
            $period = CarbonPeriod::create($impactStart->copy()->startOfDay(), $impactEnd->copy()->startOfDay());

            foreach ($period as $date) {
                $dayNumber = (int) $date->dayOfWeek; // 0 الأحد، 1 الاثنين...

                if (in_array($dayNumber, $reservationDays, true)) {
                    $impactedDates[] = $date->toDateString();
                }
            }

            if (empty($impactedDates)) {
                continue;
            }

            $sessionsCount = $this->countReservationSessions($reservation);
            $schedulePrice = optional($reservation->schedule)->price;

            if (!$schedulePrice || $sessionsCount <= 0) {
                $sessionValue = 0;
            } else {
                $sessionValue = round((float) $schedulePrice / $sessionsCount, 2);
            }

            $impactedDaysCount = count($impactedDates);
            $totalCredit = round($sessionValue * $impactedDaysCount, 2);

            $alreadyCreditedCount = ReservationCredit::where('reservation_id', $reservation->id)
                ->whereIn('closure_date', $impactedDates)
                ->count();

            $result->push([
                'reservation'             => $reservation,
                'sessions_count'          => $sessionsCount,
                'schedule_price'          => $schedulePrice,
                'session_value'           => $sessionValue,
                'impacted_dates'          => $impactedDates,
                'impacted_days_count'     => $impactedDaysCount,
                'total_credit'            => $totalCredit,
                'already_credited_count'  => $alreadyCreditedCount,
                'already_credited'        => $alreadyCreditedCount >= $impactedDaysCount,
            ]);
        }

        return $result;
    }

    private function getReservationSlotsArray(Reservation $reservation): array
    {
        if (is_array($reservation->time_slots)) {
            return $reservation->time_slots;
        }

        if (is_string($reservation->time_slots)) {
            $decoded = json_decode($reservation->time_slots, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function countReservationSessions(Reservation $reservation): int
    {
        $start = Carbon::parse($reservation->start_date)->startOfDay();
        $end   = Carbon::parse($reservation->end_date)->startOfDay();

        $slots = $this->getReservationSlotsArray($reservation);

        if (empty($slots)) {
            return 0;
        }

        $allowedDays = collect($slots)
            ->pluck('day_number')
            ->filter(fn ($day) => $day !== null)
            ->map(fn ($day) => (int) $day)
            ->unique()
            ->values()
            ->toArray();

        if (empty($allowedDays)) {
            return 0;
        }

        $period = CarbonPeriod::create($start, $end);
        $count = 0;

        foreach ($period as $date) {
            if (in_array((int) $date->dayOfWeek, $allowedDays, true)) {
                $count++;
            }
        }

        return $count;
    }
}
