<?php

namespace App\Http\Requests\ContactUser;

use App\Data\Constants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::user()->hasRole([Constants::ROLE_KEY_COACH]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'order_by' => '',
            'per_page' => '',
            'page' => '',
            'lazyQuery' => '',
        ];
    }
}
