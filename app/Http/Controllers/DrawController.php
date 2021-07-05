<?php

namespace App\Http\Controllers;

use App\Core\Rapi\Models\Draw;
use App\Core\Rapi\Transforms\DrawTransformer;
use Illuminate\Http\Request;

class DrawController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api')->except('last_results');
        $this->middleware('client.credentials')->only('last_results');
        $this->middleware('transform.input:' . DrawTransformer::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Rapi\Models\Draw $draw
     * @return \Illuminate\Http\Response
     */
    public function show(Draw $draw)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Core\Rapi\Models\Draw $draw
     * @return \Illuminate\Http\Response
     */
    public function edit(Draw $draw)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request   $request
     * @param  \App\Core\Rapi\Models\Draw $draw
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Draw $draw)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Core\Rapi\Models\Draw $draw
     * @return \Illuminate\Http\Response
     */
    public function destroy(Draw $draw)
    {
        //
    }


}
