<?php

namespace App\Core\Auth\Controllers;

use App\Core\Base\Services\TranslateTextService;
use App\Core\Base\Traits\AuthenticatesUsersTrait;
use App\Core\FreeSpin\Services\ApplyBonusFreeSpinService;
use App\Core\Users\Models\User;
use App\Http\Controllers\ApiController;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response as Psr7Response;

/**
 * Class LoginController
 * @package App\Http\Controllers\Auth
 */
class LoginController extends ApiController
{

    use AuthenticatesUsersTrait;
    use HandlesOAuthErrors;

    /**
     * @var int
     */
    public $maxAttempts = 5; // change to the max attemp you want.

    /**
     * @var int
     */
    public $decayMinutes = 5; // change to the minutes you want

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    /**
     * @var \App\Core\Users\Services\FindUserService
     */
    private $findUserService;
    /**
     * @var JwtParser
     */
    private $jwt;
    /**
     * @var AuthorizationServer
     */
    private $server;
    /**
     * @var TokenRepository
     */
    private $tokens;
    /**
     * @var \App\Core\FreeSpin\Services\ApplyBonusFreeSpinService
     */
    private $applyBonusFreeSpinService;

    /**
     * LoginController constructor.
     * @param AuthorizationServer       $server
     * @param ApplyBonusFreeSpinService $applyBonusFreeSpinService
     */
    public function __construct(
        AuthorizationServer $server,
        ApplyBonusFreeSpinService $applyBonusFreeSpinService
    ) {
        $this->middleware('check.ip');
        $this->middleware('hashed_passport');
        $this->server = $server;
        $this->applyBonusFreeSpinService = $applyBonusFreeSpinService;
    }

    /**
     * @param ServerRequestInterface $loginAuthRequest
     * @return JsonResponse|Response|null
     */
    public function login(ServerRequestInterface $loginAuthRequest)
    {
        try {
            // If the class is using the ThrottlesLogins trait, we can automatically throttle
            // the login attempts for this application. We'll key this by the username and
            // the IP address of the client making these requests into this application.

            $data = $this->withErrorHandling(
                function () use ($loginAuthRequest) {
                    return $this->convertResponse(
                        $this->server->respondToAccessTokenRequest($loginAuthRequest, new Psr7Response())
                    );
                }
            );

            $requestParams = request()->all();
            $data = (array) json_decode($data->getContent());

            if(is_array($requestParams)){
                if ( array_key_exists( 'username', $requestParams ) ) {
                    $request = Request::create( '', 'POST', $requestParams );
                    $user    = User::query()->where( 'usr_email', $requestParams[ 'username' ] )->first();

                    $arrayPromoCode = [];
                    if(array_key_exists( 'access_token', $data)){
                        $arrayPromoCode       = $this->applyBonusFreeSpinService->execute( $request, $user );
                    }
                    $data[ 'promo_code' ] = $arrayPromoCode;
                }
            }

            if ( config( 'app.debug' ) === true ) {
                $data[ 'request' ] = $requestParams;
            }

            return response()->json( $data, Response::HTTP_OK );
        }
        catch(Exception $exception) {
            $message = TranslateTextService::execute('error_login_user');
            $data['error'] = 'invalid_credentials';
            return response()->json( $data, Response::HTTP_OK );
        }

    }
}
