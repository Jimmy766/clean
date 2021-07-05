<?php

namespace App\Core\Casino\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Favorite extends CoreModel
{
    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'favorites';

    protected $primaryKey = 'id_favorite';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'id_favoriteable',
        'type_favoritable',
        'type_favorite',
        'id_user',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [];

    const CASINO     = 0;
    const SPORTBOOKS = 1;

    const KEY_CACHE_MODEL = 'FAVORITES_CACHE_';

    public const TIME_CACHE_MODEL = ModelConst::CACHE_TIME_DAY;

    protected $casts = [
        'id_favoriteable' => 'integer',
        'type_favorite'   => 'integer',
        'id_user'         => 'integer',
    ];

    public function favoriteable()
    {
        return $this->morphTo('favoriteable', 'type_favoritable', 'id_favoriteable' );
    }
}
