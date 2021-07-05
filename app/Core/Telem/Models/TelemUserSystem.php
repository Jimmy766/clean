<?php

namespace App\Core\Telem\Models;

use App\Core\Telem\Models\TelemUserGroup;
use App\Core\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Core\Telem\Traits\HasTelemProduct;

class TelemUserSystem extends Model
{
    use HasTelemProduct;

    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'users_system';

    protected $primaryKey = "sus_id";
    public $timestamps = false;



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    public function group(){
        return $this->belongsTo(TelemUserGroup::class, "sus_groupId", "group_id");
    }

    public function user() {
        return $this->belongsToMany(User::class, "telem_users_agents", "sus_id", "usr_id");
    }


}
