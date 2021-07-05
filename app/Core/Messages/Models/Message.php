<?php

namespace App\Core\Messages\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Messages\Models\MessageBatch;

/**
 * Class Message
 * @package App
 */
class Message extends CoreModel
{

    /**
     * Database table name
     */
    protected $table = 'messages';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'message_id';

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
        'batch_id',
        'message_body',
        'message_date_read',
        'message_date_received',
        'message_deleted',
        'message_read',
        'message_subject',
        'message_type',
        'usr_id',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [
        'message_date_received',
        'message_date_read',
    ];

	public function scopeActive($query, $userId)
	{
		return $query->where('message_deleted',0)
			->where('message_read',0)
			->where('message_type', '<>', 1)
			->where('usr_id', $userId);
    }
    public function batch()
    {
        return $this->belongsTo(MessageBatch::class,'batch_id');
    }


}
