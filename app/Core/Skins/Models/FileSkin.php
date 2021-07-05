<?php

namespace App\Core\Skins\Models;

use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileSkin extends CoreModel
{
    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'file_skins';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'id_file';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'tag',
        'file',
        'id_config_skin',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [];

}
