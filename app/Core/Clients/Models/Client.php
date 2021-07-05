<?php

namespace App\Core\Clients\Models;


use App\Core\Rapi\Models\Site;
use App\Core\Clients\Models\Partner;
use Illuminate\Support\Facades\Cache;

class Client extends \Laravel\Passport\Client
{
    public function partner() {
        return $this->belongsTo(Partner::class);
    }

    public function site() {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    public function getPartnerAttributesAttribute() {
        return $this->partner->transformer::transform($this->partner);
    }

    public function getSiteAttributesAttribute() {
        return $this->site->transformer::transform($this->site);
    }

    public function clients(){
       return $this->hasMany(Client::class, "parent_oauth_client_id", "id");
    }

    public function client(){
        return $this->belongsTo(Client::class, "id", "parent_oauth_client_id");
    }
}


