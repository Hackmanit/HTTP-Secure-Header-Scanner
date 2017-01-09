<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
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
            'url' => 'required|url',
            'proxy' => 'boolean',
            'proxyAddress' => 'required_with:proxy',
            'ignoreTLS' => 'boolean',
            'limitOn' => 'boolean',
            'limit' => 'required_with:limitOn|numeric|min:1',
            'doNotCrawl' => 'boolean',
            'scan.custom' => 'boolean',
            'scan.customJson' => 'required_with:scan.custom|json'
        ];
    }
}
