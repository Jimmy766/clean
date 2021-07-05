<?php

namespace App\Core\AdminLang\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\AdminLang\Models\FileText;
use Illuminate\Database\Eloquent\Model;

class File extends CoreModel
{
    protected $guarded=[];
    public $connection = 'mysql_adminlang';
    protected $table = 'files';
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


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files_text() {
        return $this->hasMany(FileText::class,'idf', 'idf');
    }
}
