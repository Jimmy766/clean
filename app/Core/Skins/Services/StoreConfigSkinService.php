<?php

namespace App\Core\Skins\Services;

use App\Core\Skins\Models\ConfigSkin;
use App\Core\Skins\Models\FileSkin;
use App\Core\Skins\Models\Skin;
use App\Core\Skins\Models\TextSkin;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreConfigSkinService
{

    public function execute(Skin $skin, Request $request)
    {
        $config = $request->config;
        $config = collect($config);

        $config = $config->map($this->mapSetSkinToConfigTransform($skin));
        $this->filesToConfig($config);
        $this->textsToConfig($config);
    }

    private function mapSetSkinToConfigTransform(Skin $skin): callable
    {
        return static function ($item, $key) use ($skin) {
            $item[ 'id_skin' ] = $skin->id_skin;

            $config = ConfigSkin::create($item);

            $item[ 'id_config_skin' ] = $config->id_config_skin;

            return $item;
        };
    }

    private function filesToConfig($configs)
    {
        foreach ($configs as $key => $config) {
            $files = $config[ 'files' ];
            $files = collect($files);
            $files = $files->map($this->mapStorefileToConfigTransform($config));
            FileSkin::insert($files->toArray());
        }
    }

    private function mapStorefileToConfigTransform($config): callable
    {
        return static function ($item, $key) use ($config) {
            $newItem[ 'id_config_skin' ] = $config[ 'id_config_skin' ];
            $newItem[ 'file' ]           = $item[ 'file' ];
            $newItem[ 'tag' ]            = $item[ 'tag' ];
            $newItem[ 'created_at' ]     = Carbon::now();
            $newItem[ 'updated_at' ]     = Carbon::now();

            return $newItem;
        };
    }

    private function textsToConfig($configs)
    {
        foreach ($configs as $key => $config) {
            $texts = $config[ 'texts' ];
            $texts = collect($texts);
            $texts = $texts->map(
                $this->mapStoretextToConfigTransform($config)
            );
            TextSkin::insert($texts->toArray());
        }
    }

    private function mapStoreTextToConfigTransform($config): callable
    {
        return static function ($item, $key) use ($config) {
            $newItem[ 'id_config_skin' ] = $config[ 'id_config_skin' ];
            $newItem[ 'text' ]           = $item[ 'text' ];
            $newItem[ 'tag' ]            = $item[ 'tag' ];
            $newItem[ 'created_at' ]     = Carbon::now();
            $newItem[ 'updated_at' ]     = Carbon::now();

            return $newItem;
        };
    }
}
