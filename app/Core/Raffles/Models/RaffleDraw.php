<?php

namespace App\Core\Raffles\Models;

use App\Core\Raffles\Models\Raffle;
use App\Core\Raffles\Models\RaffleTier;
use App\Core\Raffles\Models\RaffleTierResult;
use App\Core\Raffles\Models\RaffleTierTemplate;
use Illuminate\Database\Eloquent\Model;

class RaffleDraw extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'rff_id';
    public $timestamps = false;
    protected $table = 'raffles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [

    ];

    public function raffle() {
        return $this->belongsTo(Raffle::class, 'inf_id', 'inf_id');
    }

    public function raffle_tier_results() {
        return $this->hasMany(RaffleTierResult::class, 'rff_id', 'rff_id');
    }

    public function raffle_tier() {
        return $this->belongsTo(RaffleTier::class, 'inf_id', 'inf_id')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('updated_at', '<=', $this->rff_playdate);
                    $query->whereColumn('updated_at', 'created_at');
                });
                $query->orWhere(function ($query) {
                    $query->where('updated_at', '>=', $this->rff_playdate);
                    $query->where('created_at', '<=', $this->rff_playdate);
                });
            });

    }

    /**
     * @SWG\Definition(
     *     definition="RaffleDrawResults",
     *     @SWG\Property(
     *       property="date",
     *       type="string",
     *       format="date_time",
     *       description="Raffle Draw Date",
     *       example="2018-03-19",
     *     ),
     *     @SWG\Property(
     *       property="raffle_draw_id",
     *       type="integer",
     *       description="Raffle Draw Id",
     *       example="2181",
     *     ),
     *     @SWG\Property(
     *       property="results",
     *       type="array",
     *       description="Results",
     *       @SWG\Items(
     *         @SWG\Property(
     *           property="value",
     *           type="array",
     *           description="Picked numbers",
     *           @SWG\Items(type="integer"),
     *         ),
     *         @SWG\Property(
     *           property="serie",
     *           type="string",
     *           description="Raffle Result Serie",
     *           example="A1",
     *         ),
     *         @SWG\Property(
     *           property="fraction",
     *           type="integer",
     *           description="Raffle Result Fraction",
     *           example="5",
     *         ),
     *         @SWG\Property(
     *           property="prize",
     *           type="integer",
     *           description="Raffle Prize",
     *           example="1000000",
     *         ),
     *         @SWG\Property(
     *           property="name",
     *           type="string",
     *           description="Prize Category",
     *           example="Big Price",
     *         ),
     *       ),
     *     ),
     *  ),
     */

    public function results() {
        $templates = $this->raffle_tier->raffle_tier_templates;
        $results = collect([]);
        $templates->each(function (RaffleTierTemplate $item) use ($results) {
            $results->push($item->evaluate($this->rff_id));
        });
        return $results;
    }

    public function raffleTier() {
        return $this->belongsTo(RaffleTier::class, 'inf_id', 'inf_id')
        ;
    }
}
