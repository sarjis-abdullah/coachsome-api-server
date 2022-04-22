<?php

namespace App\Http\Requests\InviteFriend;

use App\Entities\InviteFriend;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'status' => 'required|in:' . implode(',', InviteFriend::getConstantsByPrefix('STATUS_TYPE_'))
        ];
    }
}
