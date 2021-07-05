<?php

namespace App\Core\Messages\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Messages\Models\MessageTemplate;

/**
 * Class MessageBatch
 * @package App
 */
class MessageBatch extends CoreModel
{

    /**
     * Database table name
     */
    protected $table = 'messages_batch';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'batch_id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     * The connection name for the model.
     *
     * @var string
     */
    public $connection = 'mysql_external';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'batch_recipients',
        'template_id',
        'csv_file',
        'users_list',
        'sus_id',
        'batch_date_created',
        'batch_date_scheduled',
        'batch_date_sent',
        'batch_status',
        'final_date',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [
        'batch_date_created',
        'batch_date_scheduled',
        'batch_date_sent',
        'final_date',
    ];
    public const TAG_CACHE_MODEL = 'TAG_CACHE_MESSAGE_BATCH_';
    public function messageTemplate()
    {
        return $this->belongsTo(MessageTemplate::class,'template_id');
    }
}
