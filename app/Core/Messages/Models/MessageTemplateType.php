<?php

namespace App\Core\Messages\Models;

use App\Core\Base\Models\CoreModel;

/**
 * Class MessageTemplateType
 * @package App
 */
class MessageTemplateType extends CoreModel
{

    /**
     * Database table name
     */
    protected $table = 'messages_templates_types';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'type_id';

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
        'type_name',
    ];



}
