<?php

namespace App\Core\Rapi\Models;

use App\Core\Clients\Models\Client;
use App\Core\Rapi\Transforms\AlertMailsDataTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AlertMailsData extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $table = 'alerts_mails_data';
    protected $primaryKey = 'alertMail_id';
    public $incrementing = false;
    public $timestamps = false;
    public $transformer = AlertMailsDataTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'alertMail_id',
        'lot_id',
        'send_jackpot',
        'send_results',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'alertMail_id',
        'lot_id',
        'send_jackpot',
        'send_results',
    ];

    /**
     * @param Builder $query
     * @return Builder
     */
    protected function setKeysForSaveQuery(Builder $query){
        $query
            ->where('alertMail_id', '=', $this->getAttribute('alertMail_id'))
            ->where('lot_id', '=', $this->getAttribute('lot_id'));
        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function alert_mails(){
        return $this->belongsTo(AlertMails::class, 'alertMail_id', 'alertMail_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function alert_mails_user(){
        $sys_id = Client::where('id', request()['oauth_client_id'])->first()->site->system->sys_id;
        return $this->alert_mails()
            ->where('mail','=',Auth::user()->usr_email)
            ->where('sys_id','=',$sys_id);
    }
}
