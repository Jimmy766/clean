<?php

namespace App\Core\Skins\Models;

use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramSkin extends CoreModel
{
    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'program_skins';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'id_program';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'type_range_program',
        'type_current_program',
        'period_current_program',
        'id_skin',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [];

    public function skin(): BelongsTo
    {
        return $this->belongsTo(Skin::class, 'id_skin');
    }

    public function datePrograms(): HasMany
    {
        return $this->hasMany(DateProgramsSkin::class, 'id_program');
    }

}
