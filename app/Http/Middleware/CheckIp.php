<?php

namespace App\Http\Middleware;

use App\Core\Clients\Models\Client;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Rapi\Models\Site;
use App\Core\Base\Traits\LogCache;
use App\Core\Users\Models\User;
use App\Core\Users\Models\UserLogin;
use Closure;

class CheckIp
{
    use LogCache;

    /**
     * @var \App\Core\Base\Services\SendLogConsoleService
     */
    private $sendLogConsoleService;

    public function __construct(SendLogConsoleService $sendLogConsoleService)
    {
        $this->sendLogConsoleService = $sendLogConsoleService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $t1 = round(microtime(true) * 1000);
        $exclude = collect([
            "/api/cashier",
            "/api/oauth/token",
        ]);

        if (!$exclude->contains($request->getRequestUri())) {
            $rules = [
                'user_ip' => 'required|ip',
            ];
            $request->validate($rules);
        }

        $url = $request->path();

        if($request->server('REQUEST_METHOD') === 'POST' && $url == 'api/oauth/token' && $request->username && $request->client_id) {

            if ($request->client_id > 0) {
              $client = Client::find($request->client_id);
              if (!is_object($client)) {
  		            $this->sendLogConsoleService->execute($request,'access','access', 'AUTH_ERROR:client 1st attempt ' .
                  $request->username .
                        ' / No Client found for client id: ' . $request->client_id);
                  $client = Client::where('id',$request->client_id)->first();
            		  if (!is_object($client)) {
            			  $this->sendLogConsoleService->execute($request,'access','access', 'AUTH_ERROR:client 2nd attempt ' . $request->username . ' / No Client found for client id: ' . $request->client_id);
            		  }
              }
            } else {
              $this->sendLogConsoleService->execute($request,'access','access', 'AUTH_ERROR: client_id (-1), username:  ' . $request->username );
            }


    	    if ($client && is_object($client)) {
    		      $site = $client->site;

          		if (is_object($site)) {
          			$sys_id = $site->sys_id;
          		} else {
          			// Fix this!
          			$sys_id = 1;
          		}

    	    } else {
      		// Fix this!
    		    $sys_id = 1;
    	    }

            $user = User::where('usr_email', '=', $request->username)->where('sys_id', '=', $sys_id)->first();
            if ($user && is_object($user)) {
                $user->usr_lastLogin = now()->format('Y-m-d H:i:s');
                $user->save();
                $ip = $request->user_ip === null ? '0.0.0.0' : $request->user_ip;
                $login = new UserLogin(['log_ip' => $ip, 'usr_id' => $user->usr_id]);
                $login->save();

            }

        }

        return $next($request);
    }
}
