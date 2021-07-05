<?php


namespace App\Core\AdminLang\Controllers;


use App\Http\Controllers\ApiController;
use App\Core\AdminLang\Requests\PageTranslationRequest;
use App\Core\AdminLang\Services\AL;
use App\Core\Base\Services\ClientService;


class PagesController extends ApiController
{

    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials');
    }


    public function translate($page, PageTranslationRequest $request){
        $client = ClientService::getClient($request["oauth_client_id"]);

        $lang = $request->has("lang") ? $request->get("lang") : isset($client) && isset($client->site) ? $client->site->site_lang  : "en";

        if(!$page = AL::getPage($page)){
            return $this->errorResponse("URL not found", 404);
        }

        return $this->successResponse([
            "result" => "success",
            "data" => \App\Core\AdminLang\Services\AL::translate_page($page, $request->client_sys_id, $lang)
        ]);
    }

}
