<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Transforms\SystemTransformer;
use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'sys_id';
    public $timestamps = false;
    public $transformer = SystemTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sys_name', 'code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'sys_id', 'sys_name', 'code',
    ];

    public function sites() {
        return $this->hasMany(Site::class, 'sys_id', 'sys_id');
    }

}
