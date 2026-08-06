<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Person;
use App\Models\Complex;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class PersonAuthController extends Controller
{
    /**
     * ⬅️ شاشة تسجيل الدخول
     */
    public function showLogin()
    {
        return view('auth.person.login');
    }

    /**
     * ⬅️ شاشة التسجيل — مع قائمة المجمعات
     */
    public function checkEmail(Request $request)
    {
        $email = strtolower(trim($request->input('email', '')));

        $exists = $email
            ? User::whereRaw('LOWER(email) = ?', [$email])->exists()
            : false;

        return response()->json(['available' => !$exists]);
    }
  /*  public function showRegister()
    {
        $complexes = Complex::orderBy('nom')->get();

        return view('auth.person.register', compact('complexes'));
    }*/
public function showRegister(Request $request)
{
    $complexId = $request->get('complex');
    $selectedComplex = $complexId ? Complex::find($complexId) : null;

    $complexes = Complex::orderBy('nom')->get();
    
     $activities = [];
    if ($complexId) {
        $activities = Activity::whereHas('complexActivities', function ($q) use ($complexId) {
            $q->where('complex_id', $complexId);
        })->orderBy('title')->get();
    }


    // كلمات Captcha بالفرنسية
    $words = ["ordinateur", "sport", "complexe", "terrain", "piscine", "gymnase"];
    shuffle($words);

    $correctWord = $words[0]; // نختار كلمة واحدة فقط

    // حفظ الكلمة في الجلسة
    session(['captcha_word' => $correctWord]);

    return view('auth.person.register', compact(
        'complexes', 'selectedComplex', 'complexId', 'correctWord','activities'
    ));
}



    /**
     * ⬅️ معالجة التسجيل
     */
    public function register(Request $request)
    {
       //dd($request);
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|confirmed|min:8',
            'complex_id'  => 'required|exists:complexes,id',
            'nin' => [
                'required',
                'digits:18',   // يجب أن يحتوي على 18 رقمًا بالضبط
            ],
            'privacy_policy' => 'accepted',
        ], [
            'privacy_policy.accepted' => 'يجب الموافقة على سياسة حماية البيانات',
            'nin.required'            => '❌ رقم التعريف الوطني مطلوب.',
            'nin.digits'              => '❌ رقم التعريف الوطني يجب أن يحتوي على 18 رقمًا بالضبط.',
        ]);
if (trim(strtolower($request->captcha_word)) !== strtolower(session('captcha_word'))) {
    return back()->withErrors([
        'captcha_word' => "❌ التحقق غير صحيح، يرجى إعادة المحاولة."
    ])->withInput();
}


        // إنشاء المستخدم
        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'nin' => $request->nin,
            'password'   => Hash::make($request->password),
            'type'       => 'person',
            'complex_id' => $request->complex_id,
        ]);

        // إنشاء سجل Person للشخص
        Person::create([
            'user_id'    => $user->id,
            'complex_id' => $request->complex_id,
        ]);

        // تسجيل دخول يدوي
        Auth::login($user);

        return redirect()->route('person.dashboard')
            ->with('success', 'تم إنشاء الحساب بنجاح 🎉');
    }

    /**
     * ⬅️ تسجيل الدخول
     */
    public function login(Request $request)
    {
        // قواعد التحقق
        $rules = [
            'email'    => 'required|email',
            'password' => 'required',
        ];

        // إضافة CAPTCHA خارج الـ local
        if (!app()->environment('local')) {
            $rules['g-recaptcha-response'] = 'required';
        }

        $request->validate($rules, [
            'g-recaptcha-response.required' => '⚠️ يرجى تأكيد أنك لست روبوتًا',
        ]);

        // التحقق من Google reCAPTCHA خارج local
        if (!app()->environment('local')) {
            $response = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret'   => env('RECAPTCHA_SECRET_KEY'),
                    'response' => $request->input('g-recaptcha-response'),
                    'remoteip' => $request->ip(),
                ]
            );

            if (!($response->json()['success'] ?? false)) {
                return back()
                    ->withErrors(['g-recaptcha-response' => '❌ رمز CAPTCHA غير صالح'])
                    ->withInput();
            }
        }

        // محاولة تسجيل الدخول
        if (Auth::attempt([
            'email'    => $request->email,
            'password' => $request->password,
            'type'     => 'person',
        ])) {
            $request->session()->regenerate();

            return redirect()->route('person.dashboard');
        }

        return back()->withErrors([
            'email' => '❌ بيانات الدخول غير صحيحة أو الحساب ليس فردًا',
        ]);
    }

    /**
     * ⬅️ تسجيل الخروج
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('person.login');
    }
}
