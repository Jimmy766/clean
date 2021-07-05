<?php

namespace App\Core\Users\Models;

use App\Core\Base\Models\CoreModel;

class Currency extends CoreModel
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'curr_id';
    public $timestamps = false;
    protected $table = 'currencies';

    protected $visible = [ ];
}
