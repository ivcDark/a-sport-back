<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FilterTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type'  => 'string|in:top,section_game,fields,home_guest',
            'table' => 'string|in:best_clubs'
        ];
    }

    public function messages()
    {
        return [
            'type.string'  => 'Поддерживаемый тип: string',
            'type.in'      => 'Допустимые значения: top,section_game,fields,home_guest',
            'table.string' => 'Поддерживаемый тип: string',
            'table.in'     => 'Допустимые значения: best_clubs',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status'  => 'false',
            'message' => $validator->errors()->first(),
            'data'    => [],
        ], 200);

        throw new HttpResponseException($response);
    }
}
