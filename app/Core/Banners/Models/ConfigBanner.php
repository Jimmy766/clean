<?php

namespace App\Core\Banners\Models;

use App\Core\Terms\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigBanner extends Model
{
    use SoftDeletes;
    protected $guarded=[];
    protected $table      = 'config_banners';
    protected $primaryKey = 'id_config_banner';


    public function languages(): HasMany
    {
        return $this->hasMany(Language::class, 'id_language', 'id_language');
    }
}
