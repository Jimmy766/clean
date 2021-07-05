<?php


namespace App\Core\AdminLang\Services;
use App\Core\Rapi\Services\Log;
use App\Core\AdminLang\Models\File;
use App\Core\AdminLang\Models\Section;
use App\Core\AdminLang\Models\TextTrad;
use App\Core\Base\Services\SendLogConsoleService;
use DB;

class AdminLang
{

    private $platforms = [
        1 => "triV3",
        5 => "cgi",
        "1_m" => "mobiletri",
        "5_m" => "mobilecgi"
    ];

    private function getPlatform($sys_id){
            return isset($this->platforms[$sys_id]) ? $this->platforms[$sys_id] : $this->platforms[1];
    }

    private static $instance = null;

    private function __construct(){}

    public static function getInstance(){
        if(!self::$instance){
            self::$instance =  new AdminLang();
        }
        return self::$instance;
    }

    private function getIso(){
        try{
            $lang = request()->has("client_lang") && request()->get("client_lang") != "" ? request()->get("client_lang")  : "en";
            if (strpos($lang, '-') !== false) {
                $lang = explode("-", $lang);
                if(isset($lang[0])){
                    return $lang[0];
                }
            }
            return "en";
        }catch (\Exception $ex){

            $tag = "ADMINLANG: Error when trying to get iso ". $ex->getMessage() . " Line" .
                $ex->getLine() . " ". $ex->getFile();
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(request(), 'access', 'access', $tag);
            return "en";
        }
    }

    public function translate($tag, $lang="", $plataform = "tris"){
        if($lang == ""){
            $lang = $this->getIso();
        }

        $text = TextTrad::query()->join("files", "files.idf", "=", "text_trad.idf")
            ->where("plataform", "=", $plataform)
            ->where("tag", "=", $tag)
            ->where("iso", "=", $lang)
            ->firstFromCache();

        $sendLogConsoleService = new SendLogConsoleService();
        $tag = "ADMINLANG: Translate tag|{$tag} iso|{$lang} plataform|{$plataform}";
        $sendLogConsoleService->execute(request(), 'access', 'access', $tag);

        if ($text === null) {
            return $tag;
        }
        return $text->text;
    }

    public function getSection($page, $sys_id, $iso="en"){

        if(request()->has("is_mobile") && request()->get("is_mobile")){
            $plataform = $this->getPlatform($sys_id."_m");
        }else{
            $plataform = $this->getPlatform($sys_id);
        }

        $section = \App\Core\AdminLang\Models\Section::query()
            ->where("pagina", "=", $page)
            ->getFromCache([ "id" ])
            ->pluck("id");

        $response = [
            "page" => $page,
            "translations" => [],
            "lang" => $iso,
            "system" => $sys_id
        ];

        if(!$section){
            return $response;
        }

        $files = \App\Core\AdminLang\Models\File::query()
            ->with("files_text.text_trad")
            ->whereIn("id_promo", $section)
            ->where("iso", "=", $iso)
            ->where("plataform", "=", $plataform)
            ->getFromCache();

        foreach($files as $file){
            $files_text = $file->files_text;

            foreach($files_text as $file_text){
                $response["translations"][$file_text->tag] = [
                    "tag" => optional($file_text->text_trad)->tag,
                    "translation" => optional($file_text->text_trad)->text
                ];
            }
        }

        return $response;
    }


}
