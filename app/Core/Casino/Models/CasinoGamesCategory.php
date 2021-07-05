<?php

namespace App\Core\Casino\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Casino\Models\CasinoCategory;
use App\Core\Casino\Models\CasinoGame;
use App\Core\Casino\Transforms\CasinoGamesCategoryTransformer;
use Illuminate\Database\Eloquent\Model;


class CasinoGamesCategory extends CoreModel
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'casino_games_category';
    public $timestamps = false;
    public $transformer = CasinoGamesCategoryTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order',
        'popular_game'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'casino_games_id',
        'casino_category_id',
        'order',
        'sys_id',
        'popular_game'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function casino_category() {
        return $this->belongsTo(CasinoCategory::class, 'casino_category_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function casino_game() {
        return $this->belongsTo(CasinoGame::class, 'casino_games_id', 'id')->where('game_enabled','=','1');
    }

    /**
     * @return mixed|null
     */
    public function getCasinoGameAttributesAttribute() {
        return $this->casino_game? $this->casino_game->transformer::transform($this->casino_game) : null;
    }
}
