<?php

namespace App\Http\Requests\InviteFriend;

use Illuminate\Foundation\Http\FormRequest;

class AcceptFriendInvitationRequest extends FormRequest
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
            'token' => 'exists:invite_friends'
        ];
    }
}
