<?php

namespace App\Http\Controllers;

use App\Core\Base\Traits\Pixels;

class HomeController extends Controller
{
    use Pixels;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth')->only('index');
        $this->middleware('guest')->only('welcome');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('home');
    }

    public function welcome() {
        return view('welcome');
    }
}
