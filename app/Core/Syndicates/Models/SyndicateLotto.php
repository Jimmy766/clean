<?php

namespace App\Core\Syndicates\Models;

use App\Core\Lotteries\Models\Lottery;
use App\Core\Rapi\Models\Draw;
use App\Core\Syndicates\Transforms\SyndicateLottoTransformer;
use App\Core\Rapi\Models\Wheel;
use Illuminate\Database\Eloquent\Model;

class SyndicateLotto extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'id';
    protected $table = 'syndicate_lotto';
    public $timestamps = false;
    public $transformer = SyndicateLottoTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'syndicate_id',
        'lot_id',
        'tickets',
        'wheel_id',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'syndicate_id',
        'lot_id',
        'tickets',
        'wheel_id',
    ];

    public function draws(){
        return $this->hasMany(Draw::class, 'lot_id', 'lot_id')
            ->where('draw_status', '=', 0);
    }

    public function lottery() {
        return $this->belongsTo(Lottery::class, 'lot_id', 'lot_id')
            ->where('lot_active', '=',1)
            ->where('lot_live', '=', 0);
    }

    public function getLotteryAttributesAttribute() {
        return $this->lottery ? $this->lottery->transformer::transform($this->lottery) : $this->lottery;
    }

    public function getActiveDrawAttribute() {
        return $this->draws->sortbyDesc('draw_date')->first();
        /*
        $print = Draw::where('draw_status', '=', 0)->where('lot_id', '=', $this->lot_id)
            ->orderBy('draw_date', 'desc')->get()->first();
        return Draw::where('draw_status', '=', 0)->where('lot_id', '=', $this->lot_id)
            ->orderBy('draw_date', 'desc')->get()->first();
        return $this->lottery->draws->where('draw_status', '=', 0)->sortbyDesc('draw_date')->first();
        */
    }

    public function getActiveDrawSubAttribute() {
        //return Draw::where('draw_status', '=', 0)->where('lot_id', '=', $this->lot_id)->orderBy('draw_date', 'desc')->get()->first();
        return $this->lottery->draws->where('draw_status', '=', 1)->sortbyDesc('draw_date')->first();
    }

    public function wheels(){
        return $this->hasMany(Wheel::class, "wheel_id", "wheel_id");
    }
}
