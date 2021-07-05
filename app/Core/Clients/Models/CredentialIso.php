<?php

namespace App\Core\Clients\Models;

use App\Core\Base\Models\CoreModel;

class CredentialIso extends CoreModel
{
    protected $table = 'credentials_iso';
    protected $primaryKey = 'id_credential_iso';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_credential',
        'iso'
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
