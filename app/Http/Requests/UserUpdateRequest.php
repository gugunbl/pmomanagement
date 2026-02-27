<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'name'=> 'required',
            'email' => 'required|email|unique:users,email,'.$this->user->id,
            'password' => 'nullable|confirmed|min:6',
            'id_project' => 'nullable|array',
            'id_project.*' => 'exists:m_project,id_project'
        ];
    }
}
