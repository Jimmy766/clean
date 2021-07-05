<?php

    namespace App\Core\ScratchCards\Models;

    use Illuminate\Database\Eloquent\Model;

    class ScratchCardGameToken extends Model
    {
        public $timestamps = false;
        protected $guarded = [];
        public $connection = 'mysql_external';
        protected $primaryKey = 'tokenId';
        protected $table = 'scratches_game_token';
    }
