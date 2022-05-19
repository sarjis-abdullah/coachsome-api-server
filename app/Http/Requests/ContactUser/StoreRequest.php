<?php

namespace App\Http\Requests\ContactUser;

use App\Data\Constants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return Auth::user()->hasRole([Constants::ROLE_KEY_COACH]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'receiverUserId' => "required|exists:users,id",
            'email' => [
                'required',
                Rule::unique('contact_users')->where(function ($query) {
                    $query->where('email', $this->email)
                        ->where('receiverUserId', $this->receiverUserId);
                })
            ],
            'categoryName' => "sometimes|required",
            'firstName' => "required",
            'lastName' => "required",
            'contactAbleUserId' => "sometimes|required|exists:users,id",
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Combination of Email & receiver user id is not unique',
        ];
    }
}


