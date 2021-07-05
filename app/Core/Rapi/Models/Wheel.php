<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Transforms\WheelTransformer;
use Illuminate\Database\Eloquent\Model;

class Wheel extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'wheels';
    public $timestamps = false;
    public $transformer = WheelTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pick_balls',
        'wheel_type',
        'wheel_balls',
        'wheel_lines',
        'wheel_warranty',
        'wheel_matrix',
        'wheel_consecutive_extras',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'wheel_id',
        'pick_balls',
        'wheel_type',
        'wheel_balls',
        'wheel_lines',
        'wheel_warranty',
        'wheel_matrix',
        'wheel_consecutive_extras',
    ];

    public function getTypeAttribute() {
        switch ($this->wheel_type) {
            case 1:
                return trans('lang.full_wheel');
                break;
            case 2:
                return trans('lang.abbreviated_wheel');
                break;
            default:
                return "";
        }
    }

}
