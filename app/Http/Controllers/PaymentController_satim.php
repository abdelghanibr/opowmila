<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Payment;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Person;


class PaymentController extends Controller
{
   /* public function initiate(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'g-recaptcha-response' => 'required',
            'accept_terms' => 'accepted'
        ]);

        // ✅ Google reCAPTCHA
        $captcha = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret'   => env('RECAPTCHA_SECRET_KEY'),
                'response' => $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip(),
            ]
        );

        if (!data_get($captcha->json(), 'success')) {
            return back()->withErrors([
                'g-recaptcha-response' => 'فشل التحقق الأمني'
            ]);
        }

        $reservation = Reservation::findOrFail($request->reservation_id);

        if ((float) $reservation->total_price <= 0) {
            return back()->withErrors([
                'payment' => 'مبلغ الدفع غير صالح'
            ]);
        }

        /*
         * ⚠️ SATIM غالبًا تتعامل مع أصغر وحدة للمبلغ.
         * إذا كان 1000 DZD يجب أن يرسل 100000 فاستعمل *100.
         * إذا كانت وثيقتكم تعتمد المبلغ مباشرة بالدينار فاتركها كما هي.
         
        $amount = (int) round($reservation->total_price * 100);

        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'order_id' => (string) now()->format('ymdHis'),
                'amount'   => $amount,
                'status'   => 'pending',
            ]);

            $reservation->update([
                'payment_id'     => $payment->id,
                'payment_status' => 'pending',
            ]);

            $payload = [
                'userName'          => config('services.satim.username'),
                'password'          => config('services.satim.password'),
                'orderNumber'       => $payment->order_id,
                'amount'            => $amount,
                'returnUrl'         => route('payment.verify', ['order_number' => $payment->order_id]),
                'failUrl'           => route('payment.verify', ['order_number' => $payment->order_id]),
                'description'       => 'Paiement réservation OPOW MILA #' . $reservation->id,
                'force_terminal_id' => config('services.satim.terminal_id'),
            ];

            $response = Http::asForm()
                ->timeout(30)
                ->post(config('services.satim.register_url'), $payload);

            Log::info('SATIM register response', [
                'payment_id'      => $payment->id,
                'reservation_id'  => $reservation->id,
                'http_status'     => $response->status(),
                'response_body'   => $response->body(),
                'request_payload' => $payload,
            ]);
//dd([
  //  'status' => $response->status(),
    //'body'   => $response->body(),
    //'json'   => $response->json(),
//]);
            if (! $response->successful()) {
                DB::rollBack();
                return back()->withErrors([
                    'payment' => 'خطأ في بوابة الدفع SATIM'
                ]);
            }

            $data = $response->json();

            if (!is_array($data)) {
                DB::rollBack();
                return back()->withErrors([
                    'payment' => 'استجابة SATIM غير صالحة'
                ]);
            }

            $errorCode = (string) data_get($data, 'errorCode', '');
            $formUrl   = data_get($data, 'formUrl');

            if ($errorCode !== '' && $errorCode !== '0') {
                DB::rollBack();
                return back()->withErrors([
                    'payment' => data_get($data, 'errorMessage', 'فشل تسجيل عملية الدفع')
                ]);
            }

            if (! $formUrl) {
                DB::rollBack();
                return back()->withErrors([
                    'payment' => 'رابط الدفع SATIM غير متوفر'
                ]);
            }

            $payment->update([
                'status'  => 'processing',
                'payload' => $data,
            ]);

            $reservation->update([
                'payment_status' => 'processing',
            ]);

            DB::commit();

            return redirect()->away($formUrl);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('SATIM initiate exception', [
                'reservation_id' => $request->reservation_id,
                'message'        => $e->getMessage(),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
            ]);

            return back()->withErrors([
                'payment' => 'تعذر بدء عملية الدفع عبر SATIM'
            ]);
        }
    }
*/

public function initiate(Request $request)
{
    $request->validate([
        'reservation_id' => 'required|exists:reservations,id',
        'g-recaptcha-response' => 'required',
        'accept_terms' => 'accepted'
    ]);

    $captcha = Http::asForm()->post(
        'https://www.google.com/recaptcha/api/siteverify',
        [
            'secret'   => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]
    );

    if (!data_get($captcha->json(), 'success')) {
        return back()->withErrors([
            'g-recaptcha-response' => 'فشل التحقق الأمني'
        ]);
    }

    $reservation = Reservation::findOrFail($request->reservation_id);

    // SATIM: استعمل رقم طلب قصير وصالح
    $orderNumber = str_pad((string) $reservation->id, 4, '0', STR_PAD_LEFT) . substr((string) time(), -6);

    // SATIM غالبًا بالمليم/centimes
   $amount1 = (int) round($reservation->total_price);
  
    $amount = (int) round($amount1 * 100);
//dd($amount) ;
    $payment = Payment::create([
        'order_id' => $orderNumber,
        'amount'   => $amount,
        'status'   => 'pending',
    ]);

    $reservation->update([
        'payment_id'     => $payment->id,
        'payment_status' => 'pending',
    ]);

    $payload = [
        'userName'    => config('services.satim.username'),
        'password'    => config('services.satim.password'),
        'orderNumber' => $payment->order_id,
        'amount'      => $amount,
        'currency'    => '012',
        'returnUrl'   => route('payment.verify', ['order_number' => $payment->order_id]),
        'failUrl'     => route('payment.verify', ['order_number' => $payment->order_id]),
        'description' => 'Paiement réservation OPOW MILA #' . $reservation->id,
        'language'    => 'fr',
        'jsonParams'  => json_encode([
            'force_terminal_id' => config('services.satim.terminal_id'),
            'reservation_id'    => (string) $reservation->id,
        ], JSON_UNESCAPED_UNICODE),
    ];

    $response = Http::asForm()
        ->timeout(30)
        ->post(config('services.satim.register_url'), $payload);

    if (! $response->successful()) {
        return back()->withErrors([
            'payment' => 'خطأ في بوابة الدفع SATIM'
        ]);
    }

    $data = $response->json();

    $errorCode = (string) data_get($data, 'errorCode', '');
    $errorMessage = data_get($data, 'errorMessage', 'Erreur SATIM');
    $formUrl = data_get($data, 'formUrl');
    $satimOrderId = data_get($data, 'orderId');

    if ($errorCode !== '0') {
        $payment->update([
            'status'  => 'failed',
            'payload' => $data,
        ]);

        $reservation->update([
            'payment_status' => 'failed',
        ]);

        return back()->withErrors([
            'payment' => $errorMessage . ' (code ' . $errorCode . ')'
        ]);
    }

    $payment->update([
        'status'  => 'processing',
        'payload' => $data,
        // إذا لديك هذا العمود أضفه
        // 'gateway_order_id' => $satimOrderId,
    ]);

    // إذا لم يكن لديك gateway_order_id خزنه داخل payload فقط
    if (! $formUrl) {
        return back()->withErrors([
            'payment' => 'رابط الدفع SATIM غير متوفر'
        ]);
    }

    return redirect()->away($formUrl);
}
public function verify(Request $request)
{
    $orderNumber  = $request->query('order_number') ?: $request->query('orderNumber');
    $satimOrderId = $request->query('orderId') ?: $request->query('mdOrder');

    abort_if(!$orderNumber, 400, 'Missing order number');

    // 1) Paiement local
    $payment = Payment::where('order_id', $orderNumber)->firstOrFail();

    // 2) Réservation + utilisateur
    $reservation = Reservation::where('payment_id', $payment->id)->first();
    $user = optional($reservation)->user ?? auth()->user();

    // 3) Récupérer orderId SATIM depuis le payload sauvegardé si absent dans l’URL
    $savedPayload = is_array($payment->payload)
        ? $payment->payload
        : (json_decode($payment->payload ?? '[]', true) ?: []);

    if (!$satimOrderId) {
        $satimOrderId = data_get($savedPayload, 'orderId');
    }

    // 4) Appel acknowledgeTransaction.do
    $ackPayload = [
        'userName' => config('services.satim.username'),
        'password' => config('services.satim.password'),
    ];

    if ($satimOrderId) {
        $ackPayload['orderId'] = $satimOrderId;
    } else {
        $ackPayload['orderNumber'] = $payment->order_id;
    }

    $response = Http::asForm()
        ->timeout(30)
        ->post(config('services.satim.ack_url'), $ackPayload);

    Log::info('SATIM acknowledge response', [
        'request_query' => $request->all(),
        'ack_payload'   => $ackPayload,
        'http_status'   => $response->status(),
        'header_date'   => $response->header('Date'),
        'body'          => $response->body(),
        'json'          => $response->json(),
    ]);

    // 5) Si erreur HTTP
    if (! $response->successful()) {
        return view('payments.result', [
            'payment'       => $payment,
            'reservation'   => $reservation,
            'user'          => $user,
            'status'        => 'pending',
            'action'        => 'Échec de vérification SATIM',
            'order_id'      => $satimOrderId ?: $payment->order_id,
            'approval_code' => null,
            'auth_response' => null,
            'satim_data'    => [],
        ]);
    }

    $data = $response->json();

    // 6) Si JSON invalide
    if (!is_array($data)) {
        return view('payments.result', [
            'payment'       => $payment,
            'reservation'   => $reservation,
            'user'          => $user,
            'status'        => 'pending',
            'action'        => 'Réponse SATIM invalide',
            'order_id'      => $satimOrderId ?: $payment->order_id,
            'approval_code' => null,
            'auth_response' => null,
            'satim_data'    => [],
        ]);
    }

    // 7) Lecture des champs SATIM
    $errorCode     = (string) data_get($data, 'ErrorCode', '');
    $errorMessage  = data_get($data, 'ErrorMessage', '');
    $actionCode    = (string) data_get($data, 'actionCode', '');
    $action        = data_get($data, 'actionCodeDescription', $errorMessage);
    $orderStatus   = (string) data_get($data, 'OrderStatus', '');
    $approvalCode  = data_get($data, 'approvalCode');
    $authResponse  = data_get($data, 'authorizationResponseId');

    // 8) Déterminer si paiement accepté
    $isSuccess =
        $errorCode === '0' &&
        $actionCode === '0' &&
        $orderStatus === '2';

    // 9) Date opération SATIM
    // SATIM ne renvoie pas de date claire dans le JSON, donc on prend le header Date
    $headerDate = $response->header('Date');

    if ($headerDate) {
        try {
            $operationDate = Carbon::parse($headerDate)
                ->setTimezone(config('app.timezone'));
        } catch (\Throwable $e) {
            $operationDate = now();
        }
    } else {
        $operationDate = now();
    }

    // 10) Sauvegarde paiement
    $payment->update([
        'status'        => $isSuccess ? 'success' : 'failed',
        'payload'       => $data,
        'datetimesatim' => $operationDate,
        'updated_at'    => now(),
    ]);

    // 11) Sauvegarde réservation
 /*   if ($reservation) {
        $reservation->update([
            'payment_id'     => $payment->id,
            'payment_status' => $isSuccess ? 'paid' : 'failed',
            'statut'         => $isSuccess ? 'confirmee' : $reservation->statut,
            'updated_at'     => now(),
        ]);
    } */ //modification  11-05-2026 
    
   // 11) Sauvegarde réservation

 // 11) Sauvegarde réservation
if ($reservation) {
    $reservation->update([
        'payment_id'     => $payment->id,
        'payment_status' => $isSuccess ? 'paid' : 'failed',
        'statut'         => $isSuccess ? 'confirmee' : $reservation->statut,
        'updated_at'     => now(),
    ]);

    // Si paiement accepté, remettre etat_ass à 0
    // pour la personne liée au user_id de cette réservation
    if ($isSuccess && !empty($reservation->user_id)) {
        Person::where('user_id', $reservation->user_id)
            ->update([
                'etat_ass'   => 0,
                'updated_at' => now(),
            ]);
    }
}   
    
    

    // 12) Retour vue résultat
    return view('payments.result', [
        'payment'       => $payment,
        'reservation'   => $reservation,
        'user'          => $user,
        'status'        => $isSuccess ? 'success' : 'failed',
        'action'        => $action ?: $errorMessage,
        'order_id'      => $satimOrderId ?: $payment->order_id,
        'approval_code' => $approvalCode,
        'auth_response' => $authResponse,
        'satim_data'    => $data,
    ]);
}

/*public function verify(Request $request)
{
    $orderNumber = $request->query('order_number');
    $satimOrderId = $request->query('orderId');

    abort_if(!$orderNumber && !$satimOrderId, 400, 'Missing order reference');

    $payment = null;

    if ($orderNumber) {
        $payment = Payment::where('order_id', $orderNumber)->first();
    }

    if (!$payment) {
        abort(404, 'Payment not found');
    }

    $reservation = Reservation::where('payment_id', $payment->id)->first();
    $user = optional($reservation)->user ?? auth()->user();

    $ackPayload = [
        'userName' => config('services.satim.username'),
        'password' => config('services.satim.password'),
    ];

    if ($satimOrderId) {
        $ackPayload['orderId'] = $satimOrderId;
    } else {
        $ackPayload['orderNumber'] = $payment->order_id;
    }

    $response = Http::asForm()
        ->timeout(30)
        ->post(config('services.satim.ack_url'), $ackPayload);

dd([
    'query' => $request->all(),
    'ack_payload' => $ackPayload,
    'header_date' => $response->header('Date'),
    'body' => $response->body(),
    'json' => $response->json(),
    'date_candidates' => [
        'date' => data_get($response->json(), 'date'),
        'approvedDate' => data_get($response->json(), 'approvedDate'),
        'transactionDate' => data_get($response->json(), 'transactionDate'),
        'operationDate' => data_get($response->json(), 'operationDate'),
        'depositDate' => data_get($response->json(), 'depositDate'),
        'params' => data_get($response->json(), 'params'),
    ],
]);


    if (!$response->successful()) {
        return view('payments.result', [
            'payment'       => $payment,
            'reservation'   => $reservation,
            'user'          => $user,
            'status'        => 'pending',
            'action'        => 'Échec de vérification SATIM',
            'order_id'      => $payment->order_id,
            'approval_code' => null,
        ]);
    }

    $data = $response->json();

    $errorCode     = (string) data_get($data, 'ErrorCode', '');
    $errorMessage  = data_get($data, 'ErrorMessage');
    $actionCode    = (string) data_get($data, 'actionCode', '');
    $action        = data_get($data, 'actionCodeDescription');
    $orderStatus   = (string) data_get($data, 'OrderStatus', '');
    $approvalCode  = data_get($data, 'approvalCode');
    $authResponse  = data_get($data, 'authorizationResponseId');

    $isSuccess =
        $errorCode === '0' &&
        $actionCode === '0' &&
        $orderStatus === '2';

    $payment->update([
        'status'  => $isSuccess ? 'success' : 'failed',
        'payload' => $data,
    ]);

    if ($reservation) {
        $reservation->update([
            'payment_id'     => $payment->id,
            'payment_status' => $isSuccess ? 'paid' : 'failed',
            'statut'         => $isSuccess ? 'confirmee' : $reservation->statut,
        ]);
    }

    return view('payments.result', [
        'payment'       => $payment,
        'reservation'   => $reservation,
        'user'          => $user,
        'status'        => $isSuccess ? 'success' : 'failed',
        'action'        => $action ?: $errorMessage,
        'order_id'      => $satimOrderId ?: $payment->order_id,
        'approval_code' => $approvalCode,
        'auth_response' => $authResponse,
        'satim_data'    => $data,
    ]);
}
*/
   /* public function verify(Request $request)
    {
        $orderNumber = $request->query('order_number') ?: $request->query('orderNumber');

        abort_if(! $orderNumber, 400, 'Missing order number');

        // 1️⃣ Payment local
        $payment = Payment::where('order_id', $orderNumber)->firstOrFail();

        // 2️⃣ Appel SATIM acknowledgeTransaction.do
        $response = Http::asForm()
            ->timeout(30)
            ->post(config('services.satim.ack_url'), [
                'userName'          => config('services.satim.username'),
                'password'          => config('services.satim.password'),
                'orderNumber'       => $payment->order_id,
                'force_terminal_id' => config('services.satim.terminal_id'),
            ]);

        $reservation = Reservation::where('payment_id', $payment->id)->first();
        $user = optional($reservation)->user ?? auth()->user();

        if (! $response->successful()) {
            return view('payments.result', compact(
                'payment', 'reservation', 'user'
            ))->with('status', 'pending');
        }

        $data = $response->json();

        if (!is_array($data)) {
            return view('payments.result', compact(
                'payment', 'reservation', 'user'
            ))->with('status', 'pending');
        }

        Log::info('SATIM acknowledge response', [
            'payment_id'    => $payment->id,
            'http_status'   => $response->status(),
            'response_body' => $response->body(),
        ]);

        $errorCode   = (string) data_get($data, 'errorCode', '');
        $actionCode  = (string) data_get($data, 'actionCode', '');
        $orderStatus = (string) data_get($data, 'orderStatus', data_get($data, 'OrderStatus', ''));
        $paidAt      = data_get($data, 'date') ?: data_get($data, 'approvedDate') ?: now()->toDateTimeString();

        $action = data_get($data, 'errorMessage')
            ?: data_get($data, 'actionCodeDescription')
            ?: (($errorCode === '0') ? 'Paiement confirmé' : 'Paiement refusé');

        $approval_code = data_get($data, 'approvalCode') ?: data_get($data, 'approval_code');
        $satimOrderId  = data_get($data, 'orderId') ?: $payment->order_id;

        $updatedAt = now();
        if (!empty($paidAt)) {
            try {
                $updatedAt = Carbon::parse($paidAt)->setTimezone(config('app.timezone'));
            } catch (\Exception $e) {
                logger()->warning('Invalid SATIM date', ['paidAt' => $paidAt]);
            }
        }

        $payment->update([
            'payload'       => $data,
            'updated_at'    => now(),
            'datetimesatim' => $updatedAt,
        ]);

        $isSuccess =
            $errorCode === '0' &&
            (
                $actionCode === '' ||
                $actionCode === '0' ||
                in_array($orderStatus, ['2', 'APPROVED', 'DEPOSITED'], true)
            );

        $payment->update([
            'status'        => $isSuccess ? 'success' : 'failed',
            'updated_at'    => now(),
            'datetimesatim' => $updatedAt,
        ]);

        if ($reservation) {
            $reservation->update([
                'payment_id'     => $payment->id,
                'payment_status' => $isSuccess ? 'paid' : 'failed',
                'statut'         => $isSuccess ? 'confirmee' : $reservation->statut,
                'updated_at'     => now(),
            ]);
        }

        return view('payments.result', [
            'payment'       => $payment,
            'reservation'   => $reservation,
            'user'          => $user,
            'status'        => $isSuccess ? 'success' : 'failed',
            'action'        => $action,
            'order_id'      => $satimOrderId,
            'approval_code' => $approval_code,
        ]);
    }*/

public function downloadReceipt($orderId)
{
    $payment = Payment::where('order_id', $orderId)->firstOrFail();
    $reservation = Reservation::where('payment_id', $payment->id)->first();
    $user = optional($reservation)->user;

    $satimData = is_array($payment->payload)
        ? $payment->payload
        : (json_decode($payment->payload ?? '[]', true) ?: []);

    $pdf = Pdf::loadView('payments.receipt_pdf', [
        'payment'     => $payment,
        'reservation' => $reservation,
        'user'        => $user,
        'satim_data'  => $satimData,
    ]);

    return $pdf->download('recu-paiement-' . $payment->order_id . '.pdf');
}

public function sendReceiptEmail(Request $request, $orderId)
{
    try {
        $request->validate([
            'email'    => ['required', 'email'],
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10 MB
        ]);

        $payment = Payment::where('order_id', $orderId)->firstOrFail();

        $email = $request->input('email');
        $pdfFile = $request->file('pdf_file');

        if (!$pdfFile || !$pdfFile->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier PDF invalide'
            ], 422);
        }

        $pdfBinary = file_get_contents($pdfFile->getRealPath());

        \Mail::raw('Veuillez trouver ci-joint votre reçu de paiement OPOW MILA.', function ($message) use ($email, $payment, $pdfBinary) {
            $message->to($email)
                ->subject('Reçu de paiement - ' . $payment->order_id)
                ->attachData(
                    $pdfBinary,
                    'recu-paiement-' . $payment->order_id . '.pdf',
                    ['mime' => 'application/pdf']
                );
        });

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الوصل إلى البريد: ' . $email
        ]);

    } catch (\Throwable $e) {
        \Log::error('sendReceiptEmail failed', [
            'order_id' => $orderId,
            'message'  => $e->getMessage(),
            'trace'    => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'فشل إرسال الوصل بالبريد',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    public function pay(Reservation $reservation)
    {
        if ((int)$reservation->user_id !== (int)Auth::id()) {
            abort(403, 'غير مصرح لك بالدفع لهذا الحجز');
        }

        if ($reservation->payment_status === 'paid') {
            return back()->with('info', 'ℹ️ هذا الحجز مدفوع بالفعل');
        }

        return view('payments.pay', [
            'reservation' => $reservation
        ]);
    }
}