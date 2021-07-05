<?php

namespace App\Core\Rapi\Models;

use Illuminate\Database\Eloquent\Model;

class PricePoint extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'prices_points';
    public $timestamps = false;
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
    protected $visible = [
    ];

}
