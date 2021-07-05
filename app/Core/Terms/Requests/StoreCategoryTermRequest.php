<?php

	namespace App\Core\Terms\Requests;

	use App\Core\Terms\Models\CategoryTerm;
    use App\Core\Base\Requests\Rules\CheckUniqueAttributeModel;
    use Illuminate\Foundation\Http\FormRequest;

	class StoreCategoryTermRequest extends FormRequest
	{
		public function rules()
		{
			return [
                'name'         => [
                    'required',
                    new CheckUniqueAttributeModel(new CategoryTerm, request()->term_category)
                ],
			];
		}

		public function authorize()
		{
			return true;
		}
	}
