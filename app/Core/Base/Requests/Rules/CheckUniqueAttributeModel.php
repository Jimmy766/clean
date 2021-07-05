<?php

namespace App\Core\Base\Requests\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

class CheckUniqueAttributeModel implements Rule
{
    /**
     * @var Model
     */
    private $model;
    private $attribute;
    private $instanceModel;

    /**
     * Create a new rule instance.
     *
     * @param Model $model
     * @param       $instanceModel
     */
    public function __construct(Model $model, Model $instanceModel = null)
    {
        $this->model         = $model;
        $this->instanceModel = $instanceModel;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;

        $query = $this->model::query();

        if ($this->instanceModel !== null) {
            $query = $query->whereKeyNot($this->instanceModel->getKey());
        }

        $query = $query->where($attribute, $value)
            ->first();

        if ($query !== null) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The ' . $this->attribute . ' has already been taken.');
    }
}
