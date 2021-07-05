<?php

namespace App\Http\Controllers;

use App\Core\Casino\Models\CasinoProvider;
use Illuminate\Http\Request;

class CasinoProviderController extends ApiController
{
    public function __construct(){
        parent::__construct();
        $this->middleware('auth:api')->except("index");
        $this->middleware('client.credentials')->only("index");
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $casinoProvider = CasinoProvider::where('active',1)->get();
        return $this->showAllNoPaginated($casinoProvider);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Casino\Models\CasinoProvider $casinoProvider
     * @return \Illuminate\Http\Response
     */
    public function show(CasinoProvider $casinoProvider)
    {
        //
    }
}
