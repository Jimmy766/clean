<?php

namespace App\Core\Terms\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends CoreModel
{
    use SoftDeletes;

    protected $table      = 'languages';
    protected $primaryKey = 'id_language';

    public const TAG_CACHE_MODEL = 'TAG_CACHE_LANGUAGE_';

    public const TIME_CACHE_MODEL = ModelConst::CACHE_TIME_DAY;

    protected $fillable = [
        'name',
        'code',
    ];
    protected $guarded = [];

}
