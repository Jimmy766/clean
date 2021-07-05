<?php

namespace App\Core\Casino\Models;

use App\Core\Casino\Transforms\CasinoGamesDescriptionTransformer;
use Illuminate\Database\Eloquent\Model;


class CasinoGamesDescription extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'casino_games_description';
    public $timestamps = false;
    public $transformer = CasinoGamesDescriptionTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'how_to_win',
        'active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'casino_game_id',
        'lang',
        'name',
        'description',
        'how_to_win'
    ];

}
