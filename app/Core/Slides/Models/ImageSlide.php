<?php

namespace App\Core\Slides\Models;

use App\Core\Assets\Models\Asset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImageSlide extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'image_slides';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'id_image';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'type',
        'id_slide',
        'id_asset',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    public function asset(): HasOne
    {
        return $this->hasOne(Asset::class, 'id_asset', 'id_asset');
    }
}
