<?php

namespace App\Core\Slides\Models;

use App\Core\Terms\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ConfigSlide extends Model
{

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'config_slides';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'id_config_slide';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'subtitle',
        'text_promotion',
        'description',
        'url',
        'id_language',
        'id_slide',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    public function language(): HasOne
    {
        return $this->hasOne(Language::class, 'id_language', 'id_language');
    }

}
