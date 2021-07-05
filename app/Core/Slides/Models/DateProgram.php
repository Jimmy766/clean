<?php

namespace App\Core\Slides\Models;

use App\IdProgram;
use App\Core\Slides\Models\ProgramSlide;
use App\time;
use App\timestamp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property time      $date_init    date init
 * @property time      $date_end     date end
 * @property int       $day_init     day init
 * @property int       $day_end      day end
 * @property int       $id_program   id program
 * @property timestamp $deleted_at   deleted at
 * @property timestamp $created_at   created at
 * @property timestamp $updated_at   updated at
 * @property IdProgram $programSlide belongsTo
 */
class DateProgram extends Model
{
    use SoftDeletes;

    protected $table = 'date_programs';
    protected $fillable = [
        'date_init',
        'date_end',
        'day_init',
        'day_end',
        'id_program',
    ];

    protected $dates = [
        'date_init',
        'date_end',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(ProgramSlide::class, 'id_program');
    }

}
