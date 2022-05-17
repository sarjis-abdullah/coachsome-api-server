<?php

namespace App\Http\Requests\ContactUser;

use Illuminate\Foundation\Http\FormRequest;

class NavigateContactUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|exists:contact_users,email',
            'token' => 'required|exists:contact_users,token',
        ];
    }
}
