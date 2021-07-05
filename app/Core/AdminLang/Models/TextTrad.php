<?php

namespace App\Core\AdminLang\Models;

use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\Model;

class TextTrad extends CoreModel
{
    protected $guarded=[];
    public $connection = 'mysql_adminlang';
    protected $table = 'text_trad';
    const CREATED_AT = 'ts';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [];
}
