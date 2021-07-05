<?php

namespace App\Core\SportBooks\Services;

use App\Core\Base\Traits\ConsumesExternalServiceTrait;
use App\Core\SportBooks\Services\GenerateUrlAndSendRequestSportBookPinnacleService;
use App\Core\SportBooks\Models\SportBooksGame;
use App\Core\SportBooks\Models\SportBooksProviderConfig;
use App\Core\Base\Traits\LogCache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class getUrlSportBooksService
 * @package App\Services
 */
class SearchConfigSportBooksAndGetUrlService
{

    use LogCache;
    use ConsumesExternalServiceTrait;

    private $generated;
    /**
     * @var GenerateUrlAndSendRequestSportBookPinnacleService
     */
    private $generateUrlAndSendRequestSportBookPinnacleService;

    public function __construct(
        GenerateUrlAndSendRequestSportBookPinnacleService $generateUrlAndSendRequestSportBookPinnacleService
    ) {
        $this->generateUrlAndSendRequestSportBookPinnacleService = $generateUrlAndSendRequestSportBookPinnacleService;
    }

    /**
     * @param \App\Core\SportBooks\Models\SportBooksGame $sportBookGame
     * @param                    $language
     * @param null                                       $requestUrl
     * @param                    $site
     * @return bool|string
     */
    public function execute(
        SportBooksGame $sportBookGame,
        $language,
        $requestUrl = null,
        $site
    ) {
        $response        = false;
        $relations       = [ 'providers'];
        $sportBookGame   = $sportBookGame->load($relations);
        $providers = $sportBookGame->getRelation('providers');
        $providersConfig = SportBooksProviderConfig::query()->where('sport_books_provider_id',
                $providers->id)->firstFromCache();

        if($providersConfig === null){
            throw new UnprocessableEntityHttpException(__('sport-book config provider dont exist'), null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = Auth::user();
        $response = $this->generateUrlAndSendRequestSportBookPinnacleService->execute(
            $providersConfig, $language, $user->curr_code, $response, $user, $requestUrl, $site
        );

        return $response;
    }
}

