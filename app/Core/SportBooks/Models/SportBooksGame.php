<?php

namespace App\Core\SportBooks\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Models\CoreModel;
use App\Core\SportBooks\Models\SportBooksProvider;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SportBooksGame
 * @package App
 */
class SportBooksGame extends CoreModel
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded    = [];
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    public $connection = 'mysql_external';

    /**
     *
     */
    public const TIME_CACHE_MODEL = ModelConst::CACHE_TIME_DAY;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'game_code',
        'game_rtp',
        'game_new',
        'game_enable',
        'lines',
        'multiplier',
        'sport_book_provider_id',
        'is_lobby',
        'live',
    ];

    /**
     * @param $language
     * @return string
     */
    public static function getUrlDemoDefault($language): string
    {
        return "https://fv-ooe0xga.tender88.com/{$language}/";
    }

    /**
     * @return BelongsTo
     */
    public function providers(): BelongsTo
    {
        return $this->belongsTo(
            SportBooksProvider::class,
            'sport_book_provider_id',
            'id'
        )->where('active', '=', '1');
    }
}
