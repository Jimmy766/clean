<?php

namespace App\Core\Skins\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Terms\Models\Language;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigSkin extends CoreModel
{
    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'config_skins';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'id_config_skin';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'name',
        'description',
        'id_skin',
        'id_language',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [];

    public function languages(): HasOne
    {
        return $this->hasOne(Language::class, 'id_language', 'id_language');
    }

    public function files(): HasMany
    {
        return $this->hasMany(FileSkin::class, 'id_config_skin');
    }

    public function texts(): HasMany
    {
        return $this->hasMany(TextSkin::class, 'id_config_skin');
    }
}
