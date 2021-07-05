<?php

    namespace App\Core\Rapi\Models;

    use Illuminate\Database\Eloquent\Model;

    class Movement extends Model
    {
        public $timestamps = false;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'mov_id';
    }
