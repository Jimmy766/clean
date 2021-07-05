<?php

namespace App\Core\Casino\Models;

use App\Core\Casino\Models\CasinoProviderConfig;
use App\Core\Casino\Transforms\CasinoProviderTransformer;
use Illuminate\Database\Eloquent\Model;

class CasinoProvider extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;
    public $transformer = CasinoProviderTransformer::class;

    /**
     * MultiSlot
     */
    const MULTISLOT_CASINO_PROVIDER = 1;

    /**
     * Oryx
     */
    const ORYX_CASINO_PROVIDER = 2;

    /**
     * Oryx
     */
    const REDTIGER_CASINO_PROVIDER = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'name',
        'description'
    ];

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getConfigsAttribute(){
        $provider_configs = CasinoProviderConfig::where('casino_provider_id','=',$this->id)->get();
        $configs = collect([]);
        $provider_configs->each(function ($item, $key) use ($configs) {
            $configs->put($item->key,$item->param);
        });
        return $configs;
    }

}
