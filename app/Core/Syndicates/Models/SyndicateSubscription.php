<?php

namespace App\Core\Syndicates\Models;

use App\Core\Lotteries\Models\Lottery;
use App\Core\Syndicates\Models\Syndicate;
use App\Core\Syndicates\Models\SyndicateCartSubscription;
use App\Core\Syndicates\Models\SyndicateParticipation;
use App\Core\Syndicates\Models\SyndicatePrize;
use App\Core\Rapi\Models\Draw;
use App\Core\Syndicates\Transforms\SyndicateSubscriptionTransformer;
use App\Core\Base\Traits\LogCache;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;


class SyndicateSubscription extends Model
{
    use LogCache;

    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'syndicate_sub_id';
    public $timestamps = false;
    public $transformer = SyndicateSubscriptionTransformer::class;

    protected $fillable = [
        'usr_id',
        'syndicate_id',
        'lot_id',
        'sub_tickets',
        'sub_tickets_extra',
        'sub_buydate',
        'sub_status',
        'sub_emitted',
        'sub_lastdraw_id',
        'sub_ticket_nextDraw',
        'syndicate_cts_id',
        'sub_parent',
        'sub_root',
        'sub_renew',
        'marked_for_renewal',
        'sys_id',
        'site_id',
        'sub_ticket_byDraw',
        'sus_id',
        'onhold',
        'sub_notes',
        'syndicate_picks_id',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'syndicate_sub_id',
        'usr_id',
        'syndicate_id',
        'lot_id',
        'sub_tickets',
        'sub_tickets_extra',
        'sub_buydate',
        'sub_status',
        'sub_emitted',
        'sub_lastdraw_id',
        'sub_ticket_nextDraw',
        'syndicate_cts_id',
        'sub_parent',
        'sub_root',
        'sub_renew',
        'marked_for_renewal',
        'sys_id',
        'site_id',
        'sub_ticket_byDraw',
        'sus_id',
        'onhold',
        'sub_notes',
        'syndicate_picks_id',
    ];


    public function syndicate_lottery() {
    	return $this->belongsTo(Lottery::class, 'lot_id', 'lot_id');
    }

    public function syndicate() {
        return $this->belongsTo(Syndicate::class, 'syndicate_id', 'id');
    }

    public function getParticipationsFractionsAttribute()
    {
        $syndicate = $this->syndicate;
        if($syndicate !== null){
            return $syndicate->participations_fractions;
        }
        return null;
    }

    public function syndicate_cart_subscription() {
        return $this->belongsTo(SyndicateCartSubscription::class, 'syndicate_cts_id','cts_id');
    }

    public function getOrderAttribute() {
        return $this->syndicate_cart_subscription ? $this->syndicate_cart_subscription->crt_id : null;
    }

    public function getSyndicateNameAttribute() {
        return $this->syndicate ? $this->syndicate->name : null;
    }

    public function last_draw() {
        return $this->belongsTo(Draw::class, 'sub_lastdraw_id', 'draw_id');
    }

    public function syndicate_participations() {
        return $this->hasMany(SyndicateParticipation::class, 'syndicate_sub_id', 'syndicate_sub_id');
    }

    public function getSubscriptionsAttribute() {

        return $this->sub_status != 2 && $this->syndicate && $this->syndicate->multi_lotto == 0 ? $this->sub_ticket_byDraw :
            $synd_cart_sub ? $this->syndicate_cart_subscription->cts_ticket_byDraw : null;

    }

    public function isActive() {
        return (($this->sub_tickets + $this->sub_ticket_extra > $this->sub_emitted) ||
            ($this->last_draw && ($this->last_draw->draw_status == 0 || $this->last_draw->draw_status == 2) )) &&
            $this->sub_status != 2;
    }

    public function isExpired() {
        return (($this->sub_tickets + $this->sub_ticket_extra == $this->sub_emitted) || ($this->sub_status == 2)) &&
            $this->last_draw && ($this->last_draw->draw_status != 0 && $this->last_draw->draw_status != 2);
    }

    public function getStatusAttribute() {
        return $this->isActive() ?
            trans('lang.active_subscription_tag') : trans('lang.expired_subscription_tag');
    }

    public function getSubDrawsAttribute() {
        $emitted = null;
        /* $total = 0;
        $total = SyndicateSubscription::where('syndicate_cts_id', '=', $this->syndicate_cts_id)
            ->where('syndicate_sub_id', '=', $this->syndicate_sub_id)
            ->select(DB::raw('(sub_tickets + sub_ticket_extra) / sub_ticket_byDraw as draws'), 'lot_id')
            ->distinct()
            ->get()
            ->sum('draws'); */

        if($this->sub_ticket_byDraw==0){
            return ['total' => 0];
        }

        $total = ($this->sub_tickets + $this->sub_ticket_extra) / $this->sub_ticket_byDraw;
        if ($this->sub_status != 2) {
            //Active subs

            if ($this->syndicate && $this->syndicate->multi_lotto == 0) {
                $participations = $this->syndicate_participations->isNotEmpty() ? $this->syndicate_participations()->distinct('draw_id')->count() : null;

                if ($this->last_draw && $this->last_draw->draw_status == 0) {
                    $emitted = ceil($this->sub_emitted / $this->sub_ticket_byDraw) - 1;
                    $emitted = $participations ? $participations - 1 : $emitted;
                } else {
                    $emitted = ceil($this->sub_emitted / $this->sub_ticket_byDraw);
                    $emitted = $participations ? $participations : $emitted;
                }
                $total = $participations ? $participations + ceil(($this->sub_tickets + $this->sub_ticket_extra - $this->sub_emitted) / $this->sub_ticket_byDraw) : $total;
            }
        }
        return $emitted!==null ? ['emitted' => $emitted, 'total' => $total] : ['total' => $total];
    }

    public function sindicate_prizes() {
        return $this->hasMany(SyndicatePrize::class, 'syndicate_sub_id', 'syndicate_sub_id');
    }

    public function getPrizesAttribute() {
        $prizes = [];
        $this->sindicate_prizes->each(function ($item) use (&$prizes) {
            if (isset($prizes[$item->prize_currency])) {
                $prizes[$item->prize_currency] += round($item->prize, 2);
            } else {
                $prizes[$item->prize_currency] = round($item->prize,2);
            }
        });
        $prize_array = [];
        $prizes = collect($prizes);
        $prizes->each(function ($item, $key) use (&$prize_array) {
            $prize_array []= ['currency' => $key, 'prize' => round($item,2)];
        });
        return $prize_array;
    }

    public function getLotteryAttribute() {

    	$lottery = $this->syndicate_lottery ? $this->syndicate_lottery : null;
        $region = $lottery ? $lottery->region : null;

        return [
            'identifier' => $this->lot_id,
            'name' => $lottery ? $lottery->name : null,
            'region' => $region ? $region->name : null,
        ];
    }

    private function getLineBallsManual($lottery,$draw_id,$balls) {
        $lot_ballss = collect([]);
        $lot_balls = $lottery->lot_pick_balls;
        if ($lottery->lot_id === 14 && $draw_id <= 9764) {
            $lot_balls = 5;
        }
        if ($lottery->lot_id === 15 && $draw_id <= 10166) {
            $lot_balls = 6;
        }
        for ($i = 1; $i <= $lot_balls; $i++) {
            $ball = 'tck_n'.$i;
            $lot_ballss->push($balls[$i-1]);
        }
        return $lot_ballss;
    }

    private function getLineExtraBallsManual($lottery,$draw_id,$balls) {
        $extra_balls = collect([]);
        $lot_balls = $lottery->lot_pick_balls;
        $lot_extra = $lottery->lot_pick_extra;
        if ($lottery->lot_id === 14 && $draw_id <= 9764) {
            $lot_balls = 5;
            $lot_extra = 1;
        }
        if ($lottery->lot_id === 15 && $draw_id <= 10166) {
            $lot_balls = 6;
            $lot_extra = 2;
        }
        for ($i = $lot_balls + 1; $i <= $lot_extra + $lot_balls; $i++) {
            $ball = 'tck_n'.$i;
            $extra_balls->push($balls[$i-1]);
        }
        return $extra_balls;
    }

    private function getLineRefundBallsManual($lottery,$draw_id,$balls) {
        $refund_balls = collect([]);
        $lot_balls = $lottery->lot_pick_balls;
        $lot_extra = $lottery->lot_pick_extra;
        $lot_refund = $lottery->lot_reintegro;
        if ($lottery->lot_id === 14 && $draw_id <= 9764) {
            $lot_balls = 5;
            $lot_extra = 1;
        }
        if ($lottery->lot_id === 15 && $draw_id <= 10166) {
            $lot_balls = 6;
            $lot_extra = 2;
        }
        for ($i = $lot_balls + $lot_extra + 1; $i <= $lot_extra + $lot_balls + $lot_refund; $i++) {
            $ball = 'tck_n'.$i;
            $refund_balls->push($balls[$i-1]);
        }
        return $refund_balls;
    }

    private function getMatchBallsManual($draw,$line_balls,$balls) {
        $matchs = 0;
        if ($draw->has_results()) {
            $draw->lot_balls->each(function($item, $key) use (&$matchs, $line_balls) {
                if ($line_balls->contains($item)) {
                    $matchs++;
                }
            });
        }
        return $matchs;
    }

    private function getMatchExtraBallsManual($draw,$extra_balls,$line_balls,$refund_balls,$balls) {
        $matchs = 0;
        if ($draw->has_results()) {
            if ($extra_balls->isEmpty()) {
                $extra_balls->each(function($item, $key) use (&$matchs,$line_balls) {
                    if ($line_balls->contains($item)) {
                        $matchs++;
                    }
                });
            } else {
                $draw->extra_balls->each(function($item, $key) use (&$matchs,$extra_balls) {
                    if ($extra_balls->contains($item)) {
                        $matchs++;
                    }
                });
            }

            if ($draw->has_results()) {
                $draw->refund_balls->each(function ($item, $key) use (&$matchs,$refund_balls) {
                    if ($refund_balls->contains($item)) {
                        $matchs++;
                    }
                });
            }
        }
        return $matchs;
    }


    private function getTicketsParticipationManual($conn,$draw,$sub_id,$usr_id,$syndicate_sub_id) {

      $tickets = collect([]);


      $lot_id = $draw->lot_id;
      $lottery_complete = Cache::remember('lottery_'.$lot_id, Config::get('constants.cache_5'), function () use ($lot_id) {
          return Lottery::where('lot_id', '=', $lot_id)->first();
      });

      $ticks = collect($conn->select("select tck_id,tck_raffle1,tck_n1,tck_n2,tck_n3,tck_n4,tck_n5,tck_n6,tck_n7,tck_prize_usr,curr_code
                                      from tickets where draw_id = :draw_id and sub_id = :sub_id " ,
                                        ['draw_id' => $draw->draw_id, 'sub_id' => $sub_id]));

      if ($ticks->isNotEmpty()) {
        foreach($ticks as $t) {

          $participation = [];

          $data = SyndicatePrize::where('tck_id', '=', $t->tck_id)
              ->where('usr_id', '=', $usr_id)
              ->where('syndicate_sub_id', '=', $syndicate_sub_id)
              ->first();

          $winnings = $t->tck_prize_usr;
          $curr_code = $t->curr_code;
          if ($data) {
             $winnings = $data->prize;
             $curr_code = $data->prize_currency;
          }

          $balls = array($t->tck_n1,$t->tck_n2,$t->tck_n3,$t->tck_n4,$t->tck_n5,$t->tck_n6,$t->tck_n7);

          $line_balls = $this->getLineBallsManual($lottery_complete,$draw->draw_id,$balls);
          $line_extra_balls = $this->getLineExtraBallsManual($lottery_complete,$draw->draw_id,$balls);
          $match_balls = $this->getMatchBallsManual($draw,$line_balls,$balls);
          $line_refund_balls = $this->getLineRefundBallsManual($lottery_complete,$draw->draw_id,$balls);
          $match_extra_balls = $this->getMatchExtraBallsManual($draw,$line_extra_balls,$line_balls,$line_refund_balls,$balls);

          $raffle_number = null;
          if ($lottery_complete && $lottery_complete->lot_raffle_number == 1
              && ($lottery_complete->lot_id != 8 || ($draw->draw_id < 39014 && $lottery_complete->lot_id == 8))) {
              $raffle_number = $t->tck_raffle1;
          }


          $ticket = [
              'identifier' => $t->tck_id,
              'line_balls' => $line_balls,
              'line_extra_balls' => $line_extra_balls,
              'match_balls' => $match_balls,
              'match_extra_balls' => $match_extra_balls,
              'curr_code' => $curr_code,
              'winnings' => round((float)$winnings,2),
              'raffle_number' => $raffle_number,
          ];

          $tickets->push($ticket);

        }
      }

      return $tickets;
    }

    public function getParticipationsAttribute() {
        $participations = collect([]);

        $old = true;

        if ($old) {
          $this->syndicate_participations->each(function ($item) use ($participations) {
              $participation = $item->transformer ? $item->transformer::transform($item) : $item;
              $participations->push($participation);

          });

        } else {

            // Hacer consulta contra syndicate_participation con el usr_id y el syndicate_sub_id, sacar el draw_id
            // Si no hay registro es porque no tiene participation para ese syndicate_sub_id
            $conn = DB::connection('mysql_external');
            $synd_partic = collect($conn->select("select draw_id,sub_id from syndicate_participation
                                                     where usr_id = :usr_id AND syndicate_sub_id = :syndicate_sub_id",
                                                     ['usr_id' => $this->usr_id, 'syndicate_sub_id' => $this->syndicate_sub_id]));

           if ($synd_partic->isNotEmpty()) {
             foreach($synd_partic as $p) {

               $participation = [];
               $draw_id = $p->draw_id;
               $sub_id = $p->sub_id;

               $draw_complete = Cache::remember('draw_'.$draw_id, Config::get('constants.cache_5'), function () use ($draw_id) {
                   return Draw::with("lottery")->where('draw_id', '=', $draw_id)->first();
               });

               $draw = $draw_complete ? [
                   'identifier' => (integer)$draw_complete->draw_id,
                   'date' => $draw_complete->draw_date,
                   'results' => $draw_complete->has_results() ? [
                       'pick_balls' => $draw_complete->lot_balls,
                       'extra_balls' => $draw_complete->extra_balls,
                   ] : [],
               ] : null;


               //$tickets = collect([]);
               //echo " ---- sub_id = ".$sub_id.", usr_id = ".$this->usr_id.", syndicate_sub_id =  ".$this->syndicate_sub_id."-----";
               $tickets = $this->getTicketsParticipationManual($conn,$draw_complete,$sub_id,$this->usr_id,$this->syndicate_sub_id);

               $participation = [
                  'draw' => $draw,
                  'tickets' => $tickets,
               ];

               $participations->push($participation);

             }
           }

        }

        return $participations;
    }
}
