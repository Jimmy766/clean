<?php

namespace App\Core\Messages\Models;

use App\Core\Messages\Transforms\PromotionExtraMessageTransformer;
use Illuminate\Database\Eloquent\Model;

class PromotionExtraMessage extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = ['promotion_id', 'lang'];
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'promotions_extra_message';
    public $transformer = PromotionExtraMessageTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'promotion_id',
        'lang',
        'text',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'promotion_id',
        'lang',
        'text',
    ];
}
