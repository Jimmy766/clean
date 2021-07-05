<?php

namespace App\Core\Clients\Models;

use App\Core\Clients\Transforms\PartnerTransformer;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $guarded=[];
    public $transformer = PartnerTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'revoked',
    ];
}
