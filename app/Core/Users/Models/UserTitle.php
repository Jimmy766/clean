<?php

namespace App\Core\Users\Models;

use App\Core\Users\Transforms\UserTitleTransformer;
use Illuminate\Database\Eloquent\Model;

class UserTitle extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'users_title';
    public $timestamps = false;
    public $transformer = UserTitleTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'gender',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id', 'code', 'gender',
    ];

    public function getUserGenderAttribute() {
        return $this->gender == 0 ? trans('lang.female') : trans('lang.male');
    }
}
