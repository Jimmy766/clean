<?php

namespace App\Core\Messages\Models;

use App\Core\Base\Models\CoreModel;

/**
 * Class MessageFromEmail
 * @package App
 */
class MessageFromEmail extends CoreModel
{

    /**
     * Database table name
     */
    protected $table = 'messages_from_email_addresses';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

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
        'email_address',
        'sys_id',
        'language',
    ];

}
