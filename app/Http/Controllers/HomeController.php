<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Event;
use App\Models\Activity;
use App\Models\MatchModel;
use App\Models\Complex;
use App\Models\Reservation;
use Carbon\Carbon;

class HomeController extends Controller
{

    public function welcome()
{
    $news = News::latest()->take(6)->get();
    $events = Event::orderBy('start_date')->take(6)->get();
    $activities = Activity::latest()->take(6)->get();
     // $matchesCount = MatchModel::whereIn('status', ['scheduled', 'pending'])->count(); 

$matchesCount = MatchModel::whereIn('status', ['scheduled', 'pending'])
    ->whereDate('match_date', '>=', Carbon::today())
    ->count();

$totalReservations = Reservation::count();
$totalActivities = Activity::count();
$totalComplexes = Complex::count();
$complexesByType = Complex::select('type')->get()->groupBy('type')->map->count();



    return view('welcome', compact('news','matchesCount', 'events','activities','totalReservations','totalActivities','totalComplexes','complexesByType'));
}

public function filter(Request $request)
{
    $type = $request->type;

    $complexes = Complex::where('type', $type)->get();

    return view('activities.complexes', compact('complexes', 'type'));
}

public function filterAjax($type)
{
    $complexes = Complex::where('type', $type)->get();

    return view('partials.complex-list', compact('complexes'));
}

    /**
     * Create a new controller instance.
     *
     * @return void
     */
   /* public function __construct()
    {
        $this->middleware('auth');
    }*/

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
}
