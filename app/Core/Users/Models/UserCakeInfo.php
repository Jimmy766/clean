<?php

namespace App\Core\Users\Models;

use App\Core\Users\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserCakeInfo extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'users_cake_info';
    public $timestamps = false;

    public function user() {
        return $this->belongsTo(User::class, 'usr_id', 'usr_id');
    }
}
