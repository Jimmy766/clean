<?php

namespace App\Core\SportBooks\Models;

use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

/**
 * Class SportBooksProvider
 * @package App\Core\SportBooks\Models
 */
class SportBooksProvider extends CoreModel
{
    use SoftDeletes;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public    $timestamps = false;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded    = [];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table      = 'sport_books_provider';
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    public $connection = 'mysql_external';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'name',
        'description',
    ];

    public function getConfigsAttribute()
    {
        $configs = collect([]);
        $provider_configs->each(
            function ($item, $key) use ($configs) {
                $configs->put($item->key, $item->param);
            }
        );
        return $configs;
    }
}
