<?php

namespace App\Core\AdminLang\Models;

use App\Core\AdminLang\Models\File;
use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\Model;

class Section extends CoreModel
{
    protected $guarded=[];
    public $connection = 'mysql_adminlang';
    protected $table = 'secciones';

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
    public function files() {
        return $this->hasMany(File::class,'id_promo', 'id');
    }
}
