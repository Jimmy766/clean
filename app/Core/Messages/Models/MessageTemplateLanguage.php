<?php

namespace App\Core\Messages\Models;

use App\Core\Messages\Models\MessageTemplate;
use App\Core\Base\Models\CoreModel;


class MessageTemplateLanguage extends CoreModel
{

    /**
     * Database table name
     */
    protected $table = 'messages_templates_languages';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'temp_lang_id';

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
        'subject',
        'body',
        'template_id',
        'site_id',
        'date_added',
        'language',
        'from_email_id',
    ];

    /**
     * Date time columns.
     */
    protected $dates = [
        'date_added',
    ];

    public function messageTemplate()
    {
        return $this->belongsTo(MessageTemplate::class,'template_id');
    }
}
