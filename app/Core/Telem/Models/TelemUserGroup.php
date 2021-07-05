<?php

namespace App\Core\Telem\Models;

use App\Core\Telem\Models\TelemUserSystem;
use Illuminate\Database\Eloquent\Model;

class TelemUserGroup extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'users_group';
    protected $primaryKey = "group_id";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];


    public function telem_user_agent() {
        return $this->hasMany(TelemUserSystem::class, "sus_groupId", "group_id");
    }

}
