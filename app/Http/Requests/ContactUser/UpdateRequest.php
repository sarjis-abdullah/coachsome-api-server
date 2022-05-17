<?php

namespace App\Http\Requests\ContactUser;

use App\Data\Constants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->hasRole([Constants::ROLE_KEY_COACH]);;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'categoryName' => "required"
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $allowableFields = array_merge($this->rules(), ['page' => 'number', 'per_page' => 'number', 'order_by' => 'string', 'order_direction' => 'in:asc,desc', 'include' => 'string', 'detailed' => 'string', 'propertyId' => 'number']);

            foreach ($this->all() as $key => $value) {
                if (!array_key_exists($key, $allowableFields)) {

                    // if it is a IndexRequest return invalid filter message
                    if (strpos(get_called_class(), 'IndexRequest') !== false) {
                        $validator->errors()->add($key, 'Invalid filter.');
                    } else {
                        $validator->errors()->add($key, "Field does not exist.");
                    }
                }
            }
        });
    }
}
