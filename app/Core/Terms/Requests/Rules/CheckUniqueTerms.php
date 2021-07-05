<?php

namespace App\Core\Terms\Requests\Rules;

use App\Core\Terms\Models\Term;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CheckUniqueTerms implements Rule
{
    /**
     * @var Model
     */
    private $model;
    private $attribute;
    private $instanceModel;
    /**
     * @var array
     */
    private $sites;

    /**
     * Create a new rule instance.
     *
     * @param \App\Core\Terms\Models\Term      $model
     * @param \App\Core\Terms\Models\Term|null $instanceModel
     * @param array                            $sites
     */
    public function __construct(Term $model, Term $instanceModel = null, $sites)
    {
        $this->model         = $model;
        $this->instanceModel = $instanceModel;
        $this->sites = $sites;
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
        if(!is_array($this->sites)){
            throw new UnprocessableEntityHttpException(
                __('sites_must_be_an_array'), null, Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->attribute = $attribute;

        $query = $this->model::query();

        if ($this->instanceModel !== null) {
            $query = $query->whereKeyNot($this->instanceModel->getKey());
        }
        $query=$this->querySites($query);

        $query = $query->where($attribute, $value)
            ->first();

        if ($query !== null) {
            return false;
        }
        return true;
    }

    private function querySites($queryTerm){
        $queryTerm =$queryTerm->whereHas('sites',function ($query){
            $query->whereIn('id_site',$this->sites);
        });
        return $queryTerm;
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
