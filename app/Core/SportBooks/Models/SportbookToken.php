<?php

namespace App\Core\SportBooks\Models;

use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class SportbookToken extends CoreModel
{
    public const TAG_CACHE_MODEL = 'TAG_CACHE_SPORTBOOK_TOKEN_';

    protected $guarded    = [];
    public $connection = 'mysql_external';
    protected $table      = 'sportsbook_tokens';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'usr_id',
        'site_id',
        'reg_date',
        'sportsbook_id',
        'user_code',
    ];
}
