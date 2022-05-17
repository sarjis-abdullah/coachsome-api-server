<?php

namespace App\Http\Requests\ContactUser;

use App\Data\Constants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'categoryName' => "sometimes|required",
            'firstName' => "sometimes|required",
            'lastName' => "sometimes|required",
            'email' => "required|email|unique:contact_users,email",
            'receiverUserId' => "required|exists:users,id",
            'contactAbleUserId' => "sometimes|required|exists:users,id",
        ];
    }
}
