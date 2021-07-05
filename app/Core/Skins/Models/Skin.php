<?php

namespace App\Core\Skins\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Models\CoreModel;
use App\Core\Countries\Models\RegionRapi;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skin extends CoreModel
{
    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'skins';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'id_skin';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'name',
        'status',
        'active',
    ];

    protected $casts = [
        'active' => 'integer',
        'status' => 'integer'
    ];

    public const TAG_CACHE_MODEL = 'TAG_CACHE_SKIN_';

    public const TIME_CACHE_MODEL = ModelConst::CACHE_TIME_DAY;

    /**
     * Date time columns.
     */
    protected $dates = [];
    public function configSkin()
    {
        return $this->hasMany(ConfigSkin::class, 'id_skin');
    }

    public function programSkin()
    {
        return $this->hasMany(ProgramSkin::class, 'id_skin');
    }

    public function regions()
    {
        return $this->belongsToMany(RegionRapi::class, 'region_skins', 'id_skin', 'id_region')
            ->whereNull('region_skins.deleted_at');
    }

}
