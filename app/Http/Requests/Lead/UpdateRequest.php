<?php

namespace App\Http\Requests\Lead;

use Froiden\LaravelInstaller\Request\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends CoreRequest
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

        $rules = [
            'company_name' => 'required',
            'client_name' => 'required',
            'client_email' => 'required|email|unique:leads,client_email,'.$this->route('lead'),
        ];

//        if($this->get('website') != "" && $this->get('website') != null){
//            $rules['website'] = 'regex:/^([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
//        }

        return $rules;
    }
}
