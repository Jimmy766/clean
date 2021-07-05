<?php

namespace App\Core\Skins\Models;

use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class TextSkin extends CoreModel
{
    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'text_skins';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'id_text';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'tag',
        'text',
        'id_config_skin',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [];

}
