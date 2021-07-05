<?php

namespace App\Core\Rapi\Models;

use Illuminate\Database\Eloquent\Model;

class LiveSoldByDraw extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $table = 'lotteries_sold_bydraw';
    public $timestamps = false;
}
