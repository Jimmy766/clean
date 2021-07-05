<?php

namespace App\Core\Clients\Models;

use App\Core\Clients\Models\ClientProductCountryBlacklist;
use App\Core\Base\Models\CoreModel;
use App\Core\Base\Services\DBRapiService;
use App\Core\Clients\Models\Client;
use App\Core\Rapi\Models\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;


class ClientProduct extends CoreModel
{
    protected $guarded=[];
    //public $transformer = ContinentTransformer::class;
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
    protected $hidden= [

    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product_type() {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function client_product_blacklist() {
        return $this->hasMany(ClientProductCountryBlacklist::class,'clients_products_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function client_product_type_country_blacklists(){
        return $this->hasMany(ClientProductCountryBlacklist::class,
            "product_type_id", "product_type_id");
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function client_products_country_blacklist($play = 1, $result= 1) {
        if (request('user_id') != 0) {
            $country_id = [request('client_country_id'), request('user_country')];
        } else {
            $country_id = [request('client_country_id')];
        }

        /* $blacklist = ClientProductCountryBlacklist::whereIn('country_id', $country_id)
            ->where('clients_products_id', '=', 0)
            ->where('product_type_id', '=', $this->product_type_id); */

        $blacklist = $this->client_product_type_country_blacklists
            ->whereIn('country_id', $country_id)
            ->where('clients_products_id', '=', 0);

        if ($play == 1) {
            $blacklist = $blacklist->where('play', '=', $play);
        }
        if ($result == 1) {
            $blacklist = $blacklist->where('result', '=', $result);
        }

        /* Stringify query so we can check if it exists in cache
           Cache 60 seconds

        $q = DBRapiService::stringify($blacklist);

        $blacklist = Cache::remember($q, 60, function () use($blacklist) {
            return  $blacklist->get();
        });
        */

        $blacklist = $blacklist->merge($this->client_product_blacklist->whereIn('country_id', $country_id));
        return $blacklist;
    }
}
