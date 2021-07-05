<?php

namespace App\Core\Countries\Models;

use App\Core\Base\Traits\Utils;
use App\Core\Countries\Models\Region;
use App\Core\Countries\Transforms\ContinentTransformer;
use Illuminate\Database\Eloquent\Model;


class Continent extends Model
{
    use Utils;

    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'cont_id';
    public $timestamps = false;
    public $transformer = ContinentTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cont_name_en',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'cont_id', 'cont_name_en'
    ];

    public function regions() {
        return $this->hasMany(Region::class, 'cont_id', 'cont_id');
    }

    public function getNameAttribute() {
        $name = 'cont_name_'.$this->getLanguage();
        return $this->$name ? $this->$name : $this->cont_name_en;
    }
}
