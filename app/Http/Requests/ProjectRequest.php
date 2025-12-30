<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
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
            'nama_project' => 'required',
            'nomor_spk' => 'required',
            'tanggal_spk' => 'required',
            'customer' => 'required',
            'status_implementation' => 'required',
            'pm_implementasi' => 'required',
            'status_maintenance' => 'required',
            'pm_maintenance' => 'required',

        ];

    }
}
