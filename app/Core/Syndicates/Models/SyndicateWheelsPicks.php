<?php


namespace App\Core\Syndicates\Models;


use App\Core\Syndicates\Transforms\SyndicateWheelsPicksTransformer;
use Illuminate\Database\Eloquent\Model;

class SyndicateWheelsPicks extends Model
{

    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = "syndicate_picks";
    protected $primaryKey = 'id';
    public $timestamps = false;
    public $transformer = SyndicateWheelsPicksTransformer::class;
}
