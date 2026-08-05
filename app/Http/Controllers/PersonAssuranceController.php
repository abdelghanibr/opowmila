<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\PersonAssurance;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersonAssuranceController extends Controller
{
    /**
     * Liste principale + filtres + datatable côté client.
     */
    public function index(Request $request)
    {
        $this->updateExpiredSilently();

        $status = $request->get('status', 'all');
        $search = trim((string) $request->get('search', ''));

        $query = Person::query()
            ->with(['latestAssurance', 'assuredBy'])
            ->whereHas('reservations', function ($q) {
                $q->where('payment_status', 'paid');
            });

        if ($status !== 'all') {
            $query->where('assurance_status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        $persons = $query->latest('id')->get();

        // Popup: personnes payées et jamais assurées OU sans assurance active.
        // Les personnes déjà assurées ne s'affichent pas ici: elles ont bouton renouveler.
        $candidatePersons = Person::query()
            ->with(['latestAssurance'])
            ->whereHas('reservations', function ($q) {
                $q->where('payment_status', 'paid');
            })
            ->where(function ($q) {
                $q->whereNull('assurance_status')
                    ->orWhereIn('assurance_status', ['not_assured', 'expired', 'cancelled']);
            })
            ->whereDoesntHave('assurances', function ($q) {
                $q->whereIn('status', ['pending', 'assured'])
                    ->whereDate('end_date', '>=', now()->toDateString());
            })
            ->orderBy('id', 'desc')
            ->get();

        // Date début auto = date paiement/correction: dernier paiement paid si paid_at existe, sinon updated_at, sinon aujourd'hui.
        $defaultStartDate = now()->toDateString();
        $defaultEndDate   = now()->addMonth()->toDateString();

        return view('admin.assurances.index', compact(
            'persons',
            'candidatePersons',
            'defaultStartDate',
            'defaultEndDate',
            'status',
            'search'
        ));
    }

    /**
     * Création en lot depuis popup avec checkbox plusieurs personnes.
     * Statut initial pending. Il devient assured après impression de la liste.
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'person_ids'   => 'required|array|min:1',
            'person_ids.*' => 'integer|exists:persons,id',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'note'         => 'nullable|string|max:1000',
        ]);

        $createdIds = [];

        DB::transaction(function () use ($request, &$createdIds) {
            foreach ($request->person_ids as $personId) {
                $person = Person::findOrFail($personId);
                $reservation = $this->lastPaidReservation($person);

                if (!$reservation) {
                    continue;
                }

                // Empêcher double assurance active/pending.
                $alreadyActive = PersonAssurance::where('person_id', $person->id)
                    ->whereIn('status', ['pending', 'assured'])
                    ->whereDate('end_date', '>=', now()->toDateString())
                    ->exists();

                if ($alreadyActive) {
                    continue;
                }

                $assurance = PersonAssurance::create([
                    'person_id'      => $person->id,
                    'reservation_id' => $reservation->id,
                    'start_date'     => $request->start_date,
                    'end_date'       => $request->end_date,
                    'status'         => 'pending',
                    'created_by'     => Auth::id(),
                    'note'           => $request->note,
                ]);

                $person->update([
                    'etat_ass'             => 0,
                    'assurance_status'     => 'pending',
                    'assurance_start_date' => $request->start_date,
                    'assurance_end_date'   => $request->end_date,
                    'assured_by'           => Auth::id(),
                    'assured_at'           => null,
                ]);

                $createdIds[] = $assurance->id;
            }
        });

        if (count($createdIds) === 0) {
            return back()->with('error', 'لم يتم إنشاء أي تأمين. تحقق من وجود حجوزات مدفوعة أو من عدم وجود تأمين نشط مسبقًا.');
        }

        return redirect()
            ->route('admin.assurances.index', ['status' => 'pending'])
            ->with('success', 'تم تحضير قائمة التأمين. يجب طباعتها لتأكيد الحالة كمؤمّن.');
    }

    /**
     * Renouvellement d'une personne existante dans la liste.
     */
    public function renew(Request $request, Person $person)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'note'       => 'nullable|string|max:1000',
        ]);

        $reservation = $this->lastPaidReservation($person);

        if (!$reservation) {
            return back()->with('error', 'لا يمكن تجديد التأمين: لا يوجد حجز مدفوع لهذا المنخرط.');
        }

        DB::transaction(function () use ($request, $person, $reservation) {
            PersonAssurance::where('person_id', $person->id)
                ->whereIn('status', ['pending', 'assured'])
                ->whereDate('end_date', '<', now()->toDateString())
                ->update(['status' => 'expired']);

            $assurance = PersonAssurance::create([
                'person_id'      => $person->id,
                'reservation_id' => $reservation->id,
                'start_date'     => $request->start_date,
                'end_date'       => $request->end_date,
                'status'         => 'pending',
                'created_by'     => Auth::id(),
                'note'           => $request->note,
            ]);

            $person->update([
                'etat_ass'             => 0,
                'assurance_status'     => 'pending',
                'assurance_start_date' => $assurance->start_date,
                'assurance_end_date'   => $assurance->end_date,
                'assured_by'           => Auth::id(),
                'assured_at'           => null,
            ]);
        });

        return back()->with('success', 'تم تحضير تجديد التأمين. يجب طباعة القائمة لتأكيد الحالة كمؤمّن.');
    }

    /**
     * Impression liste des assurances sélectionnées.
     * Après impression/vue print: pending devient assured.
     */
    public function printSelected(Request $request)
    {
        $ids = $request->input('assurance_ids', []);

        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        if (!is_array($ids) || count($ids) === 0) {
            return back()->with('error', 'يرجى اختيار عناصر التأمين المراد طباعتها.');
        }

        $assurances = PersonAssurance::with(['person', 'reservation', 'creator'])
            ->whereIn('id', $ids)
            ->orderBy('start_date')
            ->get();

        DB::transaction(function () use ($assurances) {
            foreach ($assurances as $assurance) {
                if ($assurance->status === 'pending') {
                    $assurance->update([
                        'status'     => 'assured',
                        'printed_at' => now(),
                        'printed_by' => Auth::id(),
                    ]);
                }

                if ($assurance->person) {
                    $assurance->person->update([
                        'etat_ass'             => 1,
                        'assurance_status'     => $assurance->end_date && $assurance->end_date->lt(now()->startOfDay()) ? 'expired' : 'assured',
                        'assurance_start_date' => $assurance->start_date,
                        'assurance_end_date'   => $assurance->end_date,
                        'assured_by'           => Auth::id(),
                        'assured_at'           => now(),
                    ]);
                }
            }
        });

        $assurances = PersonAssurance::with(['person', 'reservation', 'creator', 'printer'])
            ->whereIn('id', $ids)
            ->orderBy('start_date')
            ->get();

        return view('admin.assurances.print', compact('assurances'));
    }

    public function updateExpired()
    {
        $count = $this->updateExpiredSilently();

        return back()->with('success', 'تم تحديث التأمينات المنتهية. العدد: ' . $count);
    }

    private function updateExpiredSilently(): int
    {
        $expired = PersonAssurance::whereIn('status', ['pending', 'assured'])
            ->whereDate('end_date', '<', now()->toDateString())
            ->get();

        foreach ($expired as $assurance) {
            $assurance->update(['status' => 'expired']);

            if ($assurance->person) {
                $assurance->person->update([
                    'etat_ass'         => 0,
                    'assurance_status' => 'expired',
                ]);
            }
        }

        return $expired->count();
    }

    /**
     * Dernier paiement paid.
     * Si votre table reservations ne contient pas person_id, remplacez par user_id = $person->user_id.
     */
    private function lastPaidReservation(Person $person): ?Reservation
    {
        return Reservation::where('person_id', $person->id)
            ->where('payment_status', 'paid')
            ->latest('updated_at')
            ->first();
    }
}
