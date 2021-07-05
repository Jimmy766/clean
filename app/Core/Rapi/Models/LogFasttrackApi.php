<?php

namespace App\Core\Rapi\Models;

use Illuminate\Database\Eloquent\Model;

class LogFasttrackApi extends Model
{
    public $connection = 'mysql_external';
    protected $table = 'log_fasttrack_api_v2';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'endpoint',
        'post_data',
        'response',
    ];

}
