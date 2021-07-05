<?php

    namespace App\Core\Reports\Models;

    use App\Core\Reports\Transforms\ReportTypeTransformer;
    use Illuminate\Database\Eloquent\Model;


    class ReportType extends Model {

        protected $guarded = [];
        public $transformer = ReportTypeTransformer::class;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = ['id', 'name'];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $visible = ['id', 'name'];
    }
