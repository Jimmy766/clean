<?php

namespace App\Core\Slides\Models;

use App\Core\Slides\Models\DateProgram;
use App\IdSlide;
use App\Core\Slides\Models\Slide;
use App\timestamp;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int        $type_range_program     type range program
 * @property int        $type_current_program   type current program
 * @property int        $period_current_program period current program
 * @property int        $id_slide               id slide
 * @property timestamp  $deleted_at             deleted at
 * @property timestamp  $created_at             created at
 * @property timestamp  $updated_at             updated at
 * @property IdSlide    $slide                  belongsTo
 * @property Collection $dateprogram            belongsToMany
 */
class ProgramSlide extends Model
{

    use SoftDeletes;

    /**
     * Database table name
     */
    protected $table = 'program_slides';

    protected $primaryKey = 'id_program';

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'type_range_program',
        'type_current_program',
        'period_current_program',
        'id_slide',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [];

    public function slide(): BelongsTo
    {
        return $this->belongsTo(Slide::class, 'id_slide');
    }

    public function datePrograms(): HasMany
    {
        return $this->hasMany(DateProgram::class, 'id_program');
    }

}
