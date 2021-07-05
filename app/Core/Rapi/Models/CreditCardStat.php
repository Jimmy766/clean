<?php

namespace App\Core\Rapi\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCardStat extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'cc_id';
    protected $table = 'ccard_stats';
}
