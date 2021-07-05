<?php

namespace App\Core\Messages\Models;

use App\Core\Messages\Models\MessageTemplateType;
use App\Core\Messages\Models\MessageTemplateCategory;
use App\Core\Messages\Models\MessageTemplateLanguage;
use App\Core\Base\Models\CoreModel;

/**
 * Class MessageTemplate
 * @package App
 */
class MessageTemplate extends CoreModel
{

    /**
     * Database table name
     */
    protected $table = 'messages_templates';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'template_id';

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
        'template_name',
        'template_type',
        'template_category',
        'template_csv_tags',
        'sys_id',
        'template_active',
    ];

    public const TAG_CACHE_MODEL = 'TAG_CACHE_MESSAGE_TEMPLATE_';
    public function messageTemplateLanguage()
    {
        return $this->hasMany(MessageTemplateLanguage::class,'template_id');
    }
    public function messageTemplateCategory()
    {
        return $this->belongsTo(MessageTemplateCategory::class,'template_category','category_id');
    }
    public function messageTemplateType()
    {
        return $this->belongsTo(MessageTemplateType::class,'template_type','type_id');
    }
}
