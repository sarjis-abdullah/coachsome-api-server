<?php

namespace App\Http\Requests;

use App\Data\Constants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteFavouriteCoachRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return Auth::user()->hasRole([Constants::ROLE_ID_ATHLETE]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'coachId' => 'required|exists:users,id',
        ];
    }
}
