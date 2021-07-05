<?php

namespace App\Core\Raffles\Models;

use App\Core\Raffles\Models\RaffleDraw;
use App\Core\Raffles\Transforms\RaffleTicketTransformer;
use Illuminate\Database\Eloquent\Model;

class RaffleTicket extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'rtck_id';
    public $timestamps = false;
    protected $table = 'raffles_tickets';
    public $transformer = RaffleTicketTransformer::class;

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

    public function raffle_draw() {
        return $this->belongsTo(RaffleDraw::class, 'rff_id', 'rff_id');
    }

    public function getCurrencyAttribute() {
        return $this->raffle_draw ? $this->raffle_draw->curr_code : null;
    }

    public function getUrlAttribute() {
        $tck_encrypt = $this->rtck_id * 1476;
        $tck_encrypt = str_replace("9", "o", str_replace("8", "b", str_replace("7", "p", str_replace("6", "a", str_replace("5", "z", str_replace("4", "l", str_replace("3", "x", str_replace("2", "w", str_replace("1", "n", str_replace("0", "j", $tck_encrypt))))))))));
        return 'http://www6.trillonario.com/viewRaffleTicketSecureExtra.php?rtck_id='.$tck_encrypt;
    }

    public function getDrawDateAttribute() {
        return $this->raffle_draw ? $this->raffle_draw->rff_playdate : null;
    }

    public function getSignAttribute() {
        return 'ZODIAC_SIGN_'.$this->rtck_signo;
    }
}
