<?php

namespace App\Core\Skins\Models;

use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DateProgramsSkin extends CoreModel
{
    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'date_programs_skins';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'date_init',
        'date_end',
        'day_init',
        'day_end',
        'id_program',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [
        'date_init',
        'date_end',
    ];

    /**
     * idProgram
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function idProgram()
    {
        return $this->belongsTo(ProgramSkin::class, 'id_program');
    }

}
