<?php


namespace App\Core\AdminLang\Models;

use App\Core\AdminLang\Models\File;
use App\Core\AdminLang\Models\TextTrad;
use Illuminate\Database\Eloquent\Model;

class FileText extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_adminlang';
    protected $table = 'files_text';
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function file() {
        return $this->hasOne(File::class,'idf', 'idf');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function text_trad() {
        return $this->hasOne(TextTrad::class,'idtag', 'idtag');
    }
}
