<?php

namespace App\Core\Memberships\Models;

use App\Core\Memberships\Models\MembershipPcodeBenefit;
use Illuminate\Database\Eloquent\Model;

class MembershipAssignPool extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;
    protected $table = 'membership_assign_pool';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function membership_pcode_benefits() {
        return $this->hasMany(MembershipPcodeBenefit::class, 'pool_id', 'pool_id');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getPcodeBenefitsAttribute() {
        $pcode_benefits = collect([]);
        $this->membership_pcode_benefits->each(function ($item, $key) use ($pcode_benefits) {
            $pcode_benefits->push($item->transformer ? $item->transformer::transform($item) : $item);
        });
        return $pcode_benefits;
    }

}
