<?php

namespace App\Http\Requests;

use App\Data\Constants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFavouriteCoachRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::user()->hasRole([Constants::ROLE_KEY_ATHLETE]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'coachId' => 'required|exists:users,id',
            'isFavourite' => 'required|boolean',
        ];
    }
}
