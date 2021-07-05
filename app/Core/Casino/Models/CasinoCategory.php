<?php

namespace App\Core\Casino\Models;

use App\Core\Casino\Models\CasinoGamesCategory;
use App\Core\Clients\Models\Client;
use App\Core\Base\Models\CoreModel;
use App\Core\Base\Services\ClientService;
use App\Core\Cloud\Services\GetCloudUrlService;
use App\Core\Base\Services\GetOriginRequestService;
use App\Core\Cloud\Services\SetOriginCloudUrlService;
use App\Core\Base\Traits\ApiResponser;
use App\Core\Casino\Transforms\CasinoCategoryTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CasinoCategory extends CoreModel
{
    use ApiResponser;
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'casino_category';
    public $timestamps = false;
    public $transformer = CasinoCategoryTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'tag_name',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'name',
        'tag_name',
        'active',
        'contribution_percent',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function casino_games_category() {
        $sys_id = ClientService::getSystem(request()['oauth_client_id']);

        return $this->hasMany(CasinoGamesCategory::class, 'casino_category_id', 'id')
            ->where('sys_id','=',$sys_id)
            ->whereIn('casino_games_id', self::client_casino_games(1)->pluck('product_id'))
            ->orderBy('popular_game','desc')
            ->orderBy('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function games_category(){
        $sys_id = Client::where('id', request()['oauth_client_id'])->first()->site->system->sys_id;
        return $this->hasMany(CasinoGamesCategory::class, 'casino_category_id', 'id')->where('sys_id','=',$sys_id);
    }

    private function getCloudBaseUrl(){
        return GetCloudUrlService::execute();
    }

    public function getDemoUrlAttribute() {
        $token = request()->header('authorization');
        $token = explode(" ", $token);
        $token = $token[1];
        $token = base64_encode($token);
        $ip  = request()->user_ip;
        $urlBase =  "{$this->getCloudBaseUrl()}/games/?id={$this->id}&game_mode=demo&user_ip={$ip}&t={$token}";
        $urlBase = SetOriginCloudUrlService::execute($urlBase);

        return $urlBase;
    }

    public function getRealPlayUrlAttribute() {
        $token = request()->header('authorization');
        $token = explode(" ", $token);
        $token = $token[1];
        $token = base64_encode($token);
        $ip  = request()->user_ip;

        $url = "{$this->getCloudBaseUrl()}/games/?id={$this->id}&game_mode=real_play&user_ip={$ip}&t={$token}";
        $url = SetOriginCloudUrlService::execute($url);
        return request()->user_id ? $url : '';
    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function getCasinoGamesCategoryAttributesAttribute() {
        $games_categories = collect([]);
        $this->casino_games_category->each(function ($item, $key) use ($games_categories){
            if ($item && $item->casino_game && $item->casino_game->provider){
                $games_category = $item->transformer ? $item->transformer::transform($item) : $item;
                $games_categories->push($games_category);
            }
        });
        return $games_categories;
    }
}
