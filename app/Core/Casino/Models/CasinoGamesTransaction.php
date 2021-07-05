<?php

namespace App\Core\Casino\Models;

use App\Core\Casino\Models\CasinoProvider;
use App\Core\Casino\Transforms\CasinoGamesTransactionTransformer;
use Illuminate\Database\Eloquent\Model;


class CasinoGamesTransaction extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'multislot_transactions';
    public $timestamps = false;
    public $transformer = CasinoGamesTransactionTransformer::class;

    const BET_TRANSACTION_TYPE = "BET";
    const RESULT_TRANSACTION_TYPE = "RESULT";
    const WIN_TRANSACTION_TYPE = "WIN";
    const OPEN_TRANSACTION_STATUS = 1;
    const CLOSE_TRANSACTION_STATUS = 2;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'usr_id',
        'gameId',
        'transactionId',
        'refTransactionId',
        'transactionDate',
        'transactionStatus',
        'transactionType',
        'totalWagered',
        'balAdj',
        'balAdj_account',
        'balAdj_vip_bonus',
        'balAdj_bonus',
        'curr_code',
        'sessionId',
        'gameName',
        'casinoGameType',
        'casinoGameId',
        'description',
        'reg_date',
        'roundId',
        'tokenId',
        'casino_provider_id',
        'jackpotAmount',
        'freeRoundId',
        'gameProvider',
        'gameBetDefinition',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'usr_id',
        'gameId',
        'transactionId',
        'refTransactionId',
        'transactionDate',
        'transactionStatus',
        'transactionType',
        'totalWagered',
        'balAdj',
        'balAdj_account',
        'balAdj_vip_bonus',
        'balAdj_bonus',
        'curr_code',
        'sessionId',
        'gameName',
        'casinoGameType',
        'casinoGameId',
        'description',
        'reg_date',
        'roundId',
        'tokenId',
        'casino_provider_id',
        'jackpotAmount',
        'freeRoundId',
        'gameProvider',
        'gameBetDefinition',
    ];

    public function getTypeAttribute() {
        $type = '';
        if ($this->transactionType == self::BET_TRANSACTION_TYPE){
            $type = 'BET';
        }
        if ($this->transactionType == self::RESULT_TRANSACTION_TYPE
            || $this->transactionType == self::WIN_TRANSACTION_TYPE) {
            $type = 'WIN';
        }
        return $type;
    }
    public function getRoundsIdAttribute() {
        if ($this->casino_provider_id == CasinoProvider::MULTISLOT_CASINO_PROVIDER){
            return $this->sessionId;
        }
        if ($this->casino_provider_id == CasinoProvider::ORYX_CASINO_PROVIDER
        || $this->casino_provider_id == CasinoProvider::REDTIGER_CASINO_PROVIDER){
            return $this->roundId;
        }
    }

}
