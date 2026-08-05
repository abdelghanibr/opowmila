<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Paiement;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\Http;
use App\Models\Payment;
use Illuminate\Support\Str;

use Carbon\Carbon;
use App\Models\Person;
use App\Models\ReservationCredit;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{






public function initiate(Request $request)
{
    $request->validate([
        'reservation_id' => 'required|exists:reservations,id',
        'g-recaptcha-response' => 'required',
        'accept_terms' => 'accepted'
    ]);

    // ✅ تحقق Google reCAPTCHA
    $captcha = Http::asForm()->post(
        'https://www.google.com/recaptcha/api/siteverify',
        [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]
    );

    if (!data_get($captcha->json(), 'success')) {
        return back()->withErrors([
            'g-recaptcha-response' => 'فشل التحقق الأمني'
        ]);
    }

    // ===== تابع الدفع =====

    $reservation = Reservation::findOrFail($request->reservation_id);
    $amount = (int) round($reservation->total_price);

    $payment = Payment::create([
        'order_id' => 'ORD-' . \Str::uuid(),
        'amount'   => $amount,
        'status'   => 'pending',
    ]);

    $reservation->update([
        'payment_id'     => $payment->id,
        'payment_status' => 'pending',
    ]);

    // Guiddini
    $response = Http::withHeaders([
        'Accept'        => 'application/json',
        'Content-Type'  => 'application/json',
        'x-app-key'     => config('services.guiddini.app_key'),
        'x-app-secret'  => config('services.guiddini.secret_key'),
    ])->post('https://epay.guiddini.dz/api/payment/initiate', [
        'amount' => $amount
    ]);

    if (! $response->successful()) {
        return back()->withErrors(['payment' => 'خطأ في بوابة الدفع']);
    }

    $data = $response->json();
    $formUrl = data_get($data, 'data.attributes.form_url');

    if (! $formUrl) {
        return back()->withErrors(['payment' => 'رابط الدفع غير متوفر']);
    }

    $payment->update([
        'status'  => 'processing',
        'payload' => $data,
        'order_id'=> data_get($data, 'data.id')
    ]);

    return redirect()->away($formUrl);
}


/*
public function verify(Request $request)
{
    $orderNumber = $request->query('order_number');

    if (! $orderNumber) {
        abort(400, 'Missing order number');
    }

    // 1️⃣ جلب الدفع
    $payment = Payment::where('order_id', $orderNumber)->firstOrFail();

    // 2️⃣ استدعاء payment/show
    $response = Http::withHeaders([
        'Accept'       => 'application/json',
        'x-app-key'    => config('services.guiddini.app_key'),
        'x-app-secret' => config('services.guiddini.secret_key'),
    ])->get('https://epay.guiddini.dz/api/payment/show', [
        'order_number' => $orderNumber,
    ]);

    if (! $response->successful()) {
        return view('payments.result', [
            'payment'     => $payment,
            'reservation' => $payment->reservation,
            'user'        => optional($payment->reservation)->user,
            'status'      => 'pending',
        ]);
    }
   $reservation = Reservation::where('payment_id', $payment->id)->firstOrFail();

   
 //  dd($reservation) ;
    // 3️⃣ استخراج الحالة
    $status = data_get($response->json(), 'data.attributes.status');

    // 4️⃣ تخزين payload دائمًا
    $payment->update([
        'payload' => $response->json(),
    ]);
 $user = optional($payment->reservation)->user ?? auth()->user();
    // 5️⃣ الحالات
    if ($status === 'succeeded' || $status === 'paid') {

        $payment->update(['status' => 'success']);
        
         $reservation ->update(['payment_status' => 'paid']);

       

        return view('payments.result', [
            'payment'     => $payment,
            'reservation' => $reservation ,
            'user'        =>  $user,
            'status'      => 'success',
        ]);
    }

    if ($status === 'failed') {

        $payment->update(['status' => 'failed']);

        optional($payment->reservation)->update([
            'payment_status' => 'failed',
        ]);

        return view('payments.result', [
            'payment'     => $payment,
            'reservation' => $reservation ,
            'user'        =>  $user,
            'status'      => 'failed',
        ]);
    }
   

    // processing
    return view('payments.result', [
        'payment'     => $payment,
        'reservation' => $reservation ,
        'user'        =>  $user,
        'status'      => 'pending',
    ]);
}*/

public function verify(Request $request)
{
    $orderNumber = $request->query('order_number');

    abort_if(! $orderNumber, 400, 'Missing order number');

    // 1️⃣ Payment
    $payment = Payment::where('order_id', $orderNumber)->firstOrFail();

    // 2️⃣ API Call
    $response = Http::withHeaders([
        'Accept'       => 'application/json',
        'x-app-key'    => config('services.guiddini.app_key'),
        'x-app-secret' => config('services.guiddini.secret_key'),
    ])->get('https://epay.guiddini.dz/api/payment/show', [
        'order_number' => $orderNumber,
    ]);



    $reservation = Reservation::where('payment_id', $payment->id)->first();
    $user = optional($reservation)->user ?? auth()->user();

    // ❌ API error → pending
    if (! $response->successful()) {
        return view('payments.result', compact(
            'payment', 'reservation', 'user'
        ))->with('status', 'pending');
    }

    // 3️⃣ Status
    $status = data_get($response->json(), 'data.attributes.status');
    $paidAt    =  data_get($response->json(), 'data.attributes.updated_at');
   //  $action    =  data_get($response->json(), 'data.attributes.action_code_description');
   
   $respDesc = data_get($response->json(), 'data.attributes.params.respCode_desc');

$action = $respDesc ?: data_get(
    $response->json(),
    'data.attributes.action_code_description'
);

    $order_id =  data_get($response->json(), 'data.attributes.order_id');
     $approval_code =  data_get($response->json(), 'data.attributes.approval_code');
     
 //  dd($response->json() ) ;
    
    $updatedAt = now();

if (!empty($paidAt)) {
    try {
        $updatedAt = Carbon::parse($paidAt)
            ->setTimezone(config('app.timezone')); // مهم
    } catch (\Exception $e) {
        logger()->warning('Invalid paidAt', ['paidAt'=>$paidAt]);
    }
}
    
    
    // 4️⃣ Save payload always
    $payment->update([
        'payload' => $response->json(),
        'updated_at'   => $paidAt,
         'datetimesatim'  => $updatedAt ,
    ]);

    // 5️⃣ CONDITION UNIQUE : SUCCESS or NOT
    $isSuccess = in_array($status, ['succeeded', 'paid']);
   
$payment->update([
    'status' => $isSuccess ? 'success' : 'failed',
    'updated_at' => $paidAt,
    'datetimesatim' => $updatedAt,
]);

/*if ($reservation) {

    $reservation->update([
        'payment_id' => $payment->id, // تصحيح الربط
        'payment_status' => $isSuccess ? 'paid' : 'failed',
        'statut' => 'confirmee',
        'updated_at' => $paidAt,
    ]);

}*/

if ($reservation) {
    $reservation->update([
        'payment_id' => $payment->id, // تصحيح الربط
        'payment_status' => $isSuccess ? 'paid' : 'failed',
        'statut' => 'confirmee',
        'updated_at' => $paidAt,
    ]);

    // Si paiement accepté, remettre etat_ass à 0
    // pour la personne liée à cette réservation (ou user_id en mode historique)
    if ($isSuccess && !empty($reservation->user_id)) {
        $targetPerson = null;

        if (!empty($reservation->person_id)) {
            $targetPerson = Person::find($reservation->person_id);
        }

        if (!$targetPerson) {
            $targetPerson = Person::where('user_id', $reservation->user_id)
                ->whereNull('parent_id')
                ->first();
        }

        if ($targetPerson) {
            $targetPerson->update([
                'etat_ass'          => 0,
                'updated_at'        => now(),
                'assured_expires_on'=> now(),
            ]);
        }
    }
}  

if ($isSuccess) {
        $this->applyPendingCreditsAfterSuccessfulPayment($reservation);
   }


    return view('payments.result', [
        'payment'     => $payment,
        'reservation' => $reservation,
        'user'        => $user,
        'status'      => $isSuccess ? 'success' : 'failed',
       'action'  => $action ,
       'order_id'=> $order_id , 
         'approval_code' =>$approval_code,
    ]);
}



public function downloadReceipt($orderId)
{
    // جلب الدفع من قاعدة البيانات
    $payment = Payment::where('order_id', $orderId)->firstOrFail();

    // إذا كان الرابط محفوظ مسبقًا → افتحه مباشرة
    if ($payment->receipt_url) {
        return redirect()->away($payment->receipt_url);
    }

    // استدعاء API Guiddini
    $response = Http::withHeaders([
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
        'x-app-key'    => config('services.guiddini.app_key'),
        'x-app-secret' => config('services.guiddini.secret_key'),
    ])->get('https://epay.guiddini.dz/api/payment/receipt', [
        'order_number' => $orderId
    ]);

    if (! $response->successful()) {
        return back()->with('error', 'تعذر تحميل الوصل');
    }

    $data = $response->json();

    // Guiddini يرجع الرابط هنا
    $pdfUrl = data_get($data, 'links.href');

    if (! $pdfUrl) {
        return back()->with('error', 'رابط الوصل غير متوفر');
    }

    // حفظ الرابط في قاعدة البيانات
    $payment->update([
        'receipt_url' => $pdfUrl
    ]);

    // تحويل المستخدم للـ PDF
    return redirect()->away($pdfUrl);
}



/*public function sendReceiptEmail($orderId)
{
    $user = auth()->user();
    $email = $user->email ;
    
    if (! $email) {
        return back()->with('error', 'البريد غير متوفر');
    }
 
  $response = Http::withHeaders([
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
        'x-app-key'    => config('services.guiddini.app_key'),
        'x-app-secret' => config('services.guiddini.secret_key'),
    ])->post('https://epay.guiddini.dz/api/payment/email', [
        'order_number' => $orderId,
        'email'        => $email
    ]);
dd( $email);
 if (! $response->successful()) {
        return back()->with('error', 'فشل إرسال الوصل بالبريد');
    }

    $data = $response->json(); // ← مهم

    // يمكن حفظ حالة الإرسال
   // $payment->update([
   //     'receipt_sent_at' => now()
  //  ]);

    return back()->with('success', 'تم إرسال الوصل إلى بريدك الإلكتروني');
}
*/

public function sendReceiptEmail(Request $request, $orderId)
{

    $email = $request->input('email');

    if (! $email) {
        return back()->with('error', 'البريد غير متوفر');
    }
//dd( $email);
    $response = Http::withHeaders([
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
        'x-app-key'    => config('services.guiddini.app_key'),
        'x-app-secret' => config('services.guiddini.secret_key'),
    ])->post('https://epay.guiddini.dz/api/payment/email', [
        'order_number' => $orderId,
        'email'        => $email
    ]);

    if (! $response->successful()) {
        return back()->with('error', 'فشل إرسال الوصل بالبريد');
    }

    return back()->with('success', 'تم إرسال الوصل إلى البريد: '.$email);
}
public function pay(Reservation $reservation)
    {
        // 🔐 تأكد أن الحجز يخص المستخدم الحالي
        if ((int)$reservation->user_id !== (int)Auth::id()) {
            abort(403, 'غير مصرح لك بالدفع لهذا الحجز');
        }

        // ✅ إذا كان مدفوعًا بالفعل
        if ($reservation->payment_status === 'paid') {
            return back()->with('info', 'ℹ️ هذا الحجز مدفوع بالفعل');
        }

        // 🟡 pending أو 🔴 failed → نسمح بالدفع
        return view('payments.pay', [
            'reservation' => $reservation
        ]);
    }

 private function applyPendingCreditsAfterSuccessfulPayment(Reservation $reservation): void
{
    DB::transaction(function () use ($reservation) {

        // التحقق هل هذا الحجز استعمل من قبل أرصدة تعويضية
        $alreadyUsedForThisReservation = ReservationCredit::where('used_in_reservation_id', $reservation->id)
            ->where('status', 'used')
            ->exists();

        if ($alreadyUsedForThisReservation) {
            return;
        }

        $availableCredit = ReservationCredit::where('user_id', $reservation->user_id)
            ->where('status', 'pending')
            ->sum('credited_amount');

        if ($availableCredit <= 0) {
            return;
        }

        ReservationCredit::where('user_id', $reservation->user_id)
            ->where('status', 'pending')
            ->update([
                'status' => 'used',
                'used_in_reservation_id' => $reservation->id,
                'updated_at' => now(),
            ]);
    });
}

}
