<?php

namespace App\Core\Slides\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Slides\Models\ConfigSlide;
use App\Core\Slides\Models\ImageSlide;
use App\Core\Slides\Models\ProgramSlide;
use App\IdRegion;
use App\time;
use App\timestamp;
use App\varchar;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Core\Base\Models\CoreModel;


/**
 * @property varchar    $jack_pot     jack pot
 * @property time       $play_game_at play game at
 * @property int        $type_slide   type slide
 * @property int        $status       status
 * @property int        $active       active
 * @property int        $id_region    id region
 * @property timestamp  $deleted_at   deleted at
 * @property timestamp  $created_at   created at
 * @property timestamp  $updated_at   updated at
 * @property IdRegion   $regionSlide  belongsTo
 * @property Collection $config       belongsToMany
 * @property Collection $program      belongsToMany
 */
class Slide extends CoreModel
{

    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'slides';

    protected $primaryKey = 'id_slide';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'name',
        'jack_pot',
        'play_game_at',
        'type_slide',
        'status',
        'active',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [ 'play_game_at' ];

    /**
     * Constant to tag model data in cache.
     */
    public const TAG_CACHE_MODEL = 'TAG_CACHE_SLIDE_';

    public const TIME_CACHE_MODEL = ModelConst::CACHE_TIME_DAY;

    public function configSlide()
    {
        return $this->hasMany(ConfigSlide::class, 'id_slide');
    }

    public function programSlide()
    {
        return $this->hasOne(ProgramSlide::class, 'id_slide');
    }

    public function regions()
    {
        return $this->belongsToMany(RegionRapi::class, 'region_slide', 'id_slide', 'id_region')
            ->whereNull('region_slide.deleted_at');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ImageSlide::class, 'id_slide');
    }
}
