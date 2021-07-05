<?php

namespace App\Core\Messages\Models;

use App\Core\Base\Models\CoreModel;

/**
 * Class MessageTemplateCategory
 * @package App
 */
class MessageTemplateCategory extends CoreModel
{

    /**
     * Database table name
     */
    protected $table = 'messages_templates_categories';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'category_id';

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
        'category_name',
    ];

    public const PROMO=1;


}
