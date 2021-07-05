<?php

namespace App\Core\Raffles\Models;

use App\Core\Raffles\Models\RaffleTier;
use App\Core\Raffles\Models\RaffleTierResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class RaffleTierTemplate extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    public $timestamps = false;
    protected $table = 'raffle_tier_template';

//    public $transformer = RaffleTransformer::class;

    public function raffle_tier() {
        return $this->belongsTo(RaffleTier::class, 'id_tier', 'id');
    }

    public function raffle_tier_results() {
        return $this->hasMany(RaffleTierResult::class, 'id_rff_tier_tpl', 'id');
    }

    /**
     * Eval reg_exp and returns an array with null values in * position
     * @param $value
     * @param $reg_exp
     * @param $extraction
     * @return array
     */
    public function eval_reg_exp($value, $reg_exp, $extraction) {
        $result = [];
        $j = 0;
        for ($i = 0; $i < strlen($reg_exp); $i++) {
            if ($reg_exp[$i] == '#') {
                if ($reg_exp[0] == '*' and strlen($value) == strlen($reg_exp)) {
                    $result[$i] = $value[$i];
                } else {
                    $result[$i] = $value[$j];
                    $j++;
                }
            } else {
                $result[$i] = null;
            }
        }
        return $result;
    }

    public function eval_math_op($array, $math_op) {
        $count = count($array);
        $value = implode('', $array);
        $value = str_replace('x', $value, $math_op);
        $minus = explode('-', $value);
        $add = explode('+', $value);
        $opposite = explode('!', $value);
        $j = 0;
        if (count($minus) > 1) {
            $result = $minus[0] - $minus[1];
            $value = [];
            for ($i = 0; $i < $count; $i++) {
                if ($array[$i] == null) {
                    $value[$i] = null;
                } elseif (strlen($result) >= ($count - $i)) {
                    $value[$i] = substr($result, $j, 1);
                    $j++;
                } else {
                    $value[$i] = '0';
                }
            }
        } elseif (count($add) > 1) {
            $result = $add[0] + $add[1];
            $value = [];
            for ($i = 0; $i < $count; $i++) {
                if ($array[$i] == null) {
                    $value[$i] = null;
                } elseif (strlen($result) >= ($count - $i)) {
                    $value[$i] = substr($result, $j, 1);
                    $j++;
                } else {
                    $value[$i] = '0';
                }
            }
        } elseif (count($opposite) > 1) {
            $value = [];
            // los opuestos
            $number = $opposite[1];
            if ($array[0] != null) {
                for ($i = 0; $i < $count; $i++) {
                    if ($i < strlen($number)) {
                        $value[0][$i] = $array[$i];
                        $value[1][$i] = $array[$i];
                    } else {
                        $value[0][$i] = '0';
                        $value[1][$i] = '9';
                    }
                }
            } else {
                $value = $array;
            }
        }
        return $value;
    }

    public function eval_math_op_winning($array, $math_op) {
        $count = count($array);
        $value = implode('', $array);
        $value = str_replace('x', $value, $math_op);
        $minus = explode('-', $value);
        $add = explode('+', $value);
        $opposite = explode('!', $value);
        $j = 0;
        if (count($minus) > 1) {
            $result = $minus[0] - $minus[1];
            $value = [];
            for ($i = 0; $i < $count; $i++) {
                if ($array[$i] == null) {
                    $value[$i] = null;
                } elseif (strlen($result) >= ($count - $i)) {
                    $value[$i] = substr($result, $j, 1);
                    $j++;
                } else {
                    $value[$i] = '0';
                }
            }
        } elseif (count($add) > 1) {
            $result = $add[0] + $add[1];
            $value = [];
            for ($i = 0; $i < $count; $i++) {
                if ($array[$i] == null) {
                    $value[$i] = null;
                } elseif (strlen($result) >= ($count - $i)) {
                    $value[$i] = substr($result, $j, 1);
                    $j++;
                } else {
                    $value[$i] = '0';
                }
            }
        } elseif (count($opposite) > 1) {
          $value = $array;
        }
        return $value;
    }

    public function get_raffle_tier_results($rff_id) {
        if ($this->extraction_qty > 0) {
            $results = $this->raffle_tier_results()
                ->where('rff_id', '=', $rff_id)
                ->limit($this->extraction_qty)
                ->get();
        } else {
            $parent = RaffleTierTemplate::where('id_tier', '=', $this->id_tier)
                ->where('order', '=', $this->parent_order)
                ->first();
            $results = $parent->raffle_tier_results()
                ->where('rff_id', '=', $rff_id)
                ->limit($parent->extraction_qty)
                ->get();
        }
        return $results;
    }

    public function evaluate($rff_id) {
        $extraction = $this->extraction_qty > 0 ? true : false;
        $results = $this->get_raffle_tier_results($rff_id);
        $values = collect([]);
        $results->each(function (RaffleTierResult $item) use ($values, $extraction) {
            $value = $this->eval_reg_exp($item->value, $this->reg_exp, $extraction);
//            if ($this->id == 126) dd($value);
            if ($this->math_op != null) {
                $value = $this->eval_math_op($value, $this->math_op);
            }
            $ticket_prize = $this->prize;
            $fraction_prize = $this->prize;
            if ($item->fraccion_value == null) {
                $fraction_prize = $this->prize / 10;
            }
            $result = collect([
                'value' => $value,
                'series' => $item->serie_value,
                'fraction' => $item->fraccion_value,
                'ticket_prize' => $ticket_prize,
                'fraction_prize' => $fraction_prize,
                'name' => $this->name,
            ]);
            $values->push($result);
        });
        return $values;

    }

    public function make_ten($value) {
        $replicates = [];
        for ($i = 0; $i < 10; $i++) {
            $item = $value;
            $item[0] = ''.$i;
            $replicates []= $item;
        }
        return $replicates;
    }

    public function replace_null($array) {
        if (in_array(null, $array)) {
            if ($array[0] == null) {
                $tens = $this->make_ten($array);
                $array_temp = [];
                foreach ($tens as $ten) {
                    if (in_array(null, $ten)) {
                        $new_array = array_slice($ten, 1);
                        $array_temp1 = $this->replace_null($new_array);
                        $array_replaced = [];
                        foreach ($array_temp1 as $t) {
                            $array_replaced []= array_merge(array($ten[0]), $t);
                        }
                        $array_temp = array_merge($array_temp, $array_replaced);
                    } else {
                        $array_temp []= $ten;
                    }
                }
                return $array_temp;
            } else {
                $new_array = array_slice($array, 1);
                $array_temp1 = $this->replace_null($new_array);
                $array_replaced = [];
                foreach ($array_temp1 as $t) {
                    $array_replaced []= array_merge(array($array[0]), $t);
                }
                return $array_replaced;
            }
        } else {
            return $array;
        }
    }

    public function numbers($rff_id) {
        $numbers = collect([]);
        $extraction = $this->extraction_qty > 0 ? true : false;
        $results = $this->get_raffle_tier_results($rff_id);
        $results->each(function (RaffleTierResult $item) use ($numbers, $extraction, $rff_id) {
            $value = $this->eval_reg_exp($item->value, $this->reg_exp, $extraction);
            if ($this->math_op != null) {
                $value = $this->eval_math_op_winning($value, $this->math_op);
            }
            if (in_array(null, $value)) {
                $value = $this->replace_null($value);
            }
            if ($this->math_op == '!x') {
                $parent = RaffleTierTemplate::where('id_tier', '=', $this->id_tier)
                    ->where('order', '=', $this->parent_order)
                    ->first();
                $results = $parent->raffle_tier_results()
                    ->where('rff_id', '=', $rff_id)
                    ->limit($parent->extraction_qty)
                    ->get();
                $results->each(function (RaffleTierResult $item) use (&$value) {
                    $number = $item->value;
                    $number_array = [];
                    for ($i = 0; $i < strlen($number); $i++) {
                        $number_array []= $number[$i];
                    }
                    $value = array_filter($value, function ($item) use ($number_array) {
                        return $item != $number_array;
                    });
                    $value = array_values($value);
                });
            }
            $ticket_prize = $this->prize;
            $fraction_prize = $this->prize;
            if ($item->fraccion_value == null) {
                $fraction_prize = $this->prize / 10;
            }
            if (is_array($value[0])) {
                foreach ($value as $v) {
                    $numbers->push([
                        'value' => implode("", $v),
                        'series_value' => $item->serie_value,
                        'fraction_value' => $item->fraccion_value,
                        'ticket_prize' => $ticket_prize,
                        'fraction_prize' => $fraction_prize,
                        'name' => $this->name,
                        'parent' => $this->parent_order,
                        'math' => $this->math_op,
                        'order' => $this->order,
                    ]);
                }
            } else {
                $numbers->push([
                    'value' => implode("", $value),
                    'series_value' => $item->serie_value,
                    'fraction_value' => $item->fraccion_value,
                    'ticket_prize' => $ticket_prize,
                    'fraction_prize' => $fraction_prize,
                    'name' => $this->name,
                    'parent' => $this->parent_order,
                    'math' => $this->math_op,
                    'order' => $this->order,
                ]);
            }

        });
        return $numbers;
    }

}
