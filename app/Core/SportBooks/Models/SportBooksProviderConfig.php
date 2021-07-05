<?php

namespace App\Core\SportBooks\Models;

use App\Core\Base\Models\CoreModel;

class SportBooksProviderConfig extends CoreModel
{
    protected $table = 'sport_books_provider_configs';
    protected $primaryKey = 'id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'name',
        'description'
    ];

    public const SPORT_BOOKS_PINNACLE = 1;
}
