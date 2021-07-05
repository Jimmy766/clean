<?php

namespace App\Core\Rapi\Models;

use App\Core\Base\Models\CoreModel;

/**
 * Class TicketsProvidersInformation
 * @package App
 */
class TicketsProvidersInformation extends CoreModel
{
    protected $table = 'tickets_providers_information';
    public $connection = 'mysql_external';
}
