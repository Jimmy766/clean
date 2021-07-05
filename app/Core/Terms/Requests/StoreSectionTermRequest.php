<?php

	namespace App\Core\Terms\Requests;

	use App\Core\Base\Requests\Rules\CheckUniqueAttributeModel;
    use App\Core\Terms\Models\SectionTerm;
    use Illuminate\Foundation\Http\FormRequest;

	class StoreSectionTermRequest extends FormRequest
	{
		public function rules()
		{
			return [
                'name'         => [
                    'required',
                    new CheckUniqueAttributeModel(new SectionTerm, request()->term_section)
                ],
			];
		}

		public function authorize()
		{
			return true;
		}
	}
