<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Event;

class HomeController extends Controller
{

    public function welcome()
{
    $news = News::latest()->take(6)->get();
    $events = Event::orderBy('start_date')->take(6)->get();

    return view('welcome', compact('news', 'events'));
}
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

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
