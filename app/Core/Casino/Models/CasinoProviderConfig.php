<?php

namespace App\Core\Casino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CasinoProviderConfig extends Model
{

    protected $guarded=[];
    //public $transformer = ContinentTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden= [

    ];
}
