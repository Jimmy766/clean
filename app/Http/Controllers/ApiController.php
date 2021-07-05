<?php

namespace App\Http\Controllers;

use App\Core\Base\Traits\ApiResponser;
use App\Core\Base\Traits\Utils;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    use ApiResponser, Utils;

    public function __construct()
    {
    	$this->middleware('check.ip');
    }
}
