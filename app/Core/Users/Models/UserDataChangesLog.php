<?php

namespace App\Core\Users\Models;

use App\Core\Users\Models\User;
use Illuminate\Database\Eloquent\Model;


class UserDataChangesLog extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';

    protected $table = "users_data_changelog";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    public function user() {
        return $this->belongsTo(User::class, 'usr_id', 'usr_id');
    }
}
