<?php

namespace App\Core\Lotteries\Models;

use App\Core\Lotteries\Transforms\LotteryModifierTransformer;
use Illuminate\Database\Eloquent\Model;



class LotteryModifier extends Model
{

    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'modifier_id';
    protected $table = 'lotteries_modifiers';
    public $timestamps = false;
    public $transformer = LotteryModifierTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'modifier_id',
        'lot_id',
        'modifier_description',
        'modifier_tag',
        'mod_balls',
        'mod_extra_balls',
        'modifier_mode',
        'modifier_visible',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'modifier_id',
        'lot_id',
        'modifier_description',
        'modifier_tag',
        'mod_balls',
        'mod_extra_balls',
        'modifier_mode',
        'modifier_visible',
    ];

    public function getTagNameAttribute() {
        return "#{$this->modifier_tag}#";
    }
}
