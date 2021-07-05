<?php

namespace App\Core\Clients\Models;

use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\Model;

class ClientProductIpWhitelist extends CoreModel
{
    protected $guarded=[];
    //public $transformer = CountryTransformer::class;
    protected $table = 'clients_products_ip_whitelist';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
    ];
}
