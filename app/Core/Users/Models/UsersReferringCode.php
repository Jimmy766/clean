<?php

namespace App\Core\Users\Models;

use App\Core\Users\Models\User;
use Illuminate\Database\Eloquent\Model;

class UsersReferringCode extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'usr_id';
    public $timestamps = false;
    //public $transformer = SystemTransformer::class;

    public function user() {
        return $this->belongsTo(User::class, 'usr_id', 'usr_id');
    }
}
