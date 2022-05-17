<?php

namespace App\Http\Requests\ContactUser;

use Illuminate\Foundation\Http\FormRequest;

class ActivateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
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
            'id' => 'required|exists:contact_users,id',
            'email' => 'required|exists:users,email',
        ];
    }
}
